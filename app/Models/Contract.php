<?php


namespace App\Models;


use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Contract  extends SnipeModel
{
    protected $presenter = 'App\Presenters\ContractPresenter';
    use Presentable;

    protected $dates = ['deleted_at'];
    protected $table = 'contracts';
    protected $rules = array(
        'name' => 'required|min:2|max:255|unique_undeleted',
        'bitrix_id' => 'min:1|nullable'
    );


    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var boolean
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;
    use UniqueUndeletedTrait;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', //NAME
        'number', //UF_NUMBER
        'status', //STATUS_ID
        'date_start',//DATE_START
        'date_end', // DATE_END
        'bitrix_id', //ID
        'type', //ID
    ];


    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'account_number', 'comments', 'bitrix_id'];


    public function getTypeText()
    {
        switch ($this->type) {
            case 1:
                return "Пульт";
                break;
            case 2:
                return "Пульт-аренда";
                break;
            case 12:
                return "Пульт+то";
                break;
            case 3:
                return "Физ.охрана";
                break;
            case 4:
                return "Тех. обслуживание";
                break;
            case 5:
                return "Монтаж";
                break;
            case 6:
                return "Подрядчики";
                break;
            case 7:
                return "Экстренная помощь";
                break;
            case 8:
                return "Биометрика";
                break;
            case 9:
                return "Клининг";
                break;
            case 10:
                return "Разовая услуга";
                break;
            case 11:
                return "Личная охрана";
                break;
            case 13:
                return "Пожарная сигнализация ТО";
                break;
            default:
                return "";
        }
    }
    public function getStatusText()
    {

        switch ($this->status) {
            case "PREPARING":
                return "Готовится";
                break;
            case 1:
                return "На подписи";
                break;
            case 6:
                return "Подписан нами";
                break;
            case 7:
                return "Подписан клиентом";
                break;
            case "SIGNED_BOTH":
                return "Подписан всеми";
                break;
            case "TERMINATED":
                return "Расторгнут";
                break;
            case 9:
                return "Расторжение";
                break;
            case 8:
                return "Завершен";
                break;
            default:
                return "";
        }
    }

}