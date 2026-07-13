<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        if (! Company::count()) {
            $this->call(CompanySeeder::class);
        }

        $companyIds = Company::all()->pluck('id');

        if (! Department::count()) {
            $this->call(DepartmentSeeder::class);
        }

        $departmentIds = Department::all()->pluck('id');

        // Named admins get multiple companies. They manage assets across several organisations.
        foreach (['firstAdmin', 'snipeAdmin', 'testAdmin'] as $state) {
            $user = User::factory()->{$state}()->withoutCompany()->create([
                'department_id' => $departmentIds->random(),
            ]);
            $ids = $companyIds->random(min(rand(2, 3), $companyIds->count()))->toArray();
            $user->companies()->sync($ids);
            $user->syncLegacyCompanyIdMirror();
        }

        // Superusers, one company each.
        User::factory()->count(3)->superuser()
            ->withoutCompany()
            ->state(new Sequence(fn ($sequence) => [
                'department_id' => $departmentIds->random(),
            ]))
            ->create()
            ->each(function (User $user) use ($companyIds) {
                $user->companies()->sync([$companyIds->random()]);
                $user->syncLegacyCompanyIdMirror();
            });

        // Admins, one company each.
        User::factory()->count(3)->admin()
            ->withoutCompany()
            ->state(new Sequence(fn ($sequence) => [
                'department_id' => $departmentIds->random(),
            ]))
            ->create()
            ->each(function (User $user) use ($companyIds) {
                $user->companies()->sync([$companyIds->random()]);
                $user->syncLegacyCompanyIdMirror();
            });

        // Regular users, three groups:
        //   ~30% (600) no company
        //   ~50% (1000) one company
        //   ~20% (400) two or three companies

        User::factory()->count(600)->viewAssets()
            ->withoutCompany()
            ->state(new Sequence(fn ($sequence) => [
                'department_id' => $departmentIds->random(),
            ]))
            ->create();

        User::factory()->count(1000)->viewAssets()
            ->withoutCompany()
            ->state(new Sequence(fn ($sequence) => [
                'department_id' => $departmentIds->random(),
            ]))
            ->create()
            ->each(function (User $user) use ($companyIds) {
                $user->companies()->sync([$companyIds->random()]);
                $user->syncLegacyCompanyIdMirror();
            });

        $multiCompanyUsers = User::factory()->count(400)->viewAssets()
            ->withoutCompany()
            ->state(new Sequence(fn ($sequence) => [
                'department_id' => $departmentIds->random(),
            ]))
            ->create();

        foreach ($multiCompanyUsers as $user) {
            $ids = $companyIds->random(min(rand(2, 3), $companyIds->count()))->toArray();
            $user->companies()->sync($ids);
            $user->syncLegacyCompanyIdMirror();
        }

        $src = public_path('/img/demo/avatars/');
        $dst = 'avatars'.'/';
        $del_files = Storage::files($dst);

        foreach ($del_files as $del_file) { // iterate files
            $file_to_delete = str_replace($src, '', $del_file);
            Log::debug('Deleting: '.$file_to_delete);
            try {
                Storage::disk('public')->delete($dst.$del_file);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $add_files = glob($src.'/*.*');
        foreach ($add_files as $add_file) {
            $file_to_copy = str_replace($src, '', $add_file);
            Log::debug('Copying: '.$file_to_copy);
            try {
                Storage::disk('public')->put($dst.$file_to_copy, file_get_contents($src.$file_to_copy));
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $users = User::orderBy('id', 'asc')->take(20)->get();
        $file_number = 1;

        foreach ($users as $user) {

            $user->avatar = $file_number.'.jpg';
            $user->save();
            $file_number++;
        }

    }
}
