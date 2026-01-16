<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // $this->call(CategoriesTableSeeder::class);
        // $this->call(MediaTableSeeder::class);

        // Permissions and Roles are handled in DemoSeeder or can be kept if they exist. 
        // For safety/simplicity in this demo, let's rely on DemoSeeder for the core flow.

        $this->call(DemoSeeder::class);

        /*
         php artisan iseed categories,category_exercise,category_post,cfs,check_exercises,course_users,courses,exercise_comments,exercises,group_users,groups,media,model_has_permissions,model_has_roles,mps,permissions,posts,qualifications,ras,role_has_permissions,roles,sub_type_exercises,task_exercises,task_users,tasks,type_checks,type_exercises,type_tasks --exclude=created_at,updated_at --force

        attempts, userts

        */

    }
}
