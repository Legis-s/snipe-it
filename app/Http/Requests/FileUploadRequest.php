<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Http\Requests\Request;
use App\Models\SnipeModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class FileUploadRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $maxFileSizeKb = (int) ceil(Helper::file_upload_max_size() / 1024);

        return [
            'invoice_file' => 'required|file|mimes:png,gif,jpg,svg,jpeg,doc,docx,pdf,txt,zip,rar,xls,xlsx,rtf,lic|max:'.$maxFileSizeKb,
        ];
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }

    /**
     * Handle and store any images attached to request
     * @param SnipeModel $item Item the image is associated with
     * @param String $path  location for uploaded images, defaults to uploads/plural of item type.
     * @return SnipeModel        Target asset is being checked out to.
     */
    public function handleFile($item,$path = null)
    {

        $type = strtolower(class_basename(get_class($item)));

        if (is_null($path)) {
            $path =  str_plural($type);
        }

        \Log::debug('Trying to upload to '. $path);

        if ($this->hasFile('invoice_file')) {

            File::ensureDirectoryExists($path, 0755, true);
            $file = $this->file('invoice_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeName = Str::slug($basename) ?: 'invoice';
            $file_name = 'file_'.$safeName.'-'.Str::random(8).'.'.$extension;
            \Log::debug('File name will be: '.$file_name);

            \Log::debug('Trying to upload to: '.$path.'/'.$file_name);

            $file->move($path, $file_name);
            if (! is_file($path.'/'.$file_name)) {
                throw new RuntimeException('Не удалось сохранить файл счёта на сервере.');
            }

            // Remove Current image if exists
            if (($item->invoice_file) && (file_exists($path.'/'.$item->invoice_file))) {
                try {
                    unlink($path.'/'.$item->invoice_file);
                } catch (\Exception $e) {
                    \Log::debug($e);
                }
            }

            $item->invoice_file = $file_name;

        } elseif ($this->input('file_delete')=='1') {

            try {
                unlink($path.'/'.$item->invoice_file);
            } catch (\Exception $e) {
                \Log::debug($e);
            }

            $item->invoice_file = null;
        }
        return $item;
    }
}
