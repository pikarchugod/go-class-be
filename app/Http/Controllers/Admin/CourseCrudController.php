<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CourseRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Auth;

class CourseCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * 基本設定：指定 Model、Route、Entity 名稱
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Course::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/course');
        CRUD::setEntityNameStrings('course', 'courses');

        // 預載 teacher, category，避免 N+1
        $this->crud->with(['teacher', 'category']);

        // 若是 teacher，僅顯示 teacher_id=自己
        if (Auth::check() && Auth::user()->role === 'teacher') {
            $this->crud->addClause('where', 'teacher_id', Auth::id());
        }
    }

    /**
     * 列表頁面
     */
    protected function setupListOperation()
    {
        // 先自動根據資料表欄位生成欄位
        CRUD::setFromDb();

        // Teacher → 顯示教師名字
        CRUD::modifyColumn('teacher_id', [
            'label' => 'Teacher',
            'type'  => 'text',
            'value' => function ($entry) {
                return $entry->teacher ? $entry->teacher->name : 'No teacher';
            },
        ]);

        // Category → 顯示分類名字
        CRUD::modifyColumn('category_id', [
            'label' => 'Category',
            'type'  => 'text',
            'value' => function ($entry) {
                return $entry->category ? $entry->category->name : 'No category';
            },
        ]);

        // Cover Image → 顯示縮圖
        CRUD::modifyColumn('cover_image', [
            'label'  => 'Cover Image',
            'type'   => 'image',
            'disk'   => 'public',
            //'prefix' => 'storage/',   // DB 只存相對路徑 (e.g. uploads/courses/xxx.jpg)
            'height' => '60px',
            'width'  => '60px',
        ]);
    }

    /**
     * 新增頁面
     */
    protected function setupCreateOperation()
    {
        // 若有自訂驗證可啟用
        // CRUD::setValidation(CourseRequest::class);

        // 根據 DB 欄位自動生成
        CRUD::setFromDb();

        // Teacher 欄位：下拉選單（若使用者為 teacher 則隱藏）
        CRUD::field('teacher_id')
            ->type('select')
            ->label('Teacher')
            ->entity('teacher')                // 關聯方法 teacher()
            ->model("App\\Models\\User")
            ->attribute('name')
            ->options(function ($query) {
                return $query->where('role', 'teacher')->get();
            });

        if (Auth::check() && Auth::user()->role === 'teacher') {
            CRUD::field('teacher_id')
                ->type('hidden')
                ->value(Auth::id());
        }

        // Category 欄位：下拉選單
        CRUD::field('category_id')
            ->type('select')
            ->label('Category')
            ->entity('category')               // 關聯方法 category()
            ->model("App\\Models\\Category")
            ->attribute('name');

        // Cover Image 欄位：上傳
        // 使用 Backpack 6.8 新的 withFiles() 寫法
        CRUD::field('cover_image')
            ->type('upload')
            ->label('封面圖片')
            ->withFiles([
                'disk' => 'public',
                'path' => 'uploads/courses',
            ]);
    }

    /**
     * 更新頁面
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }


    // public function store()
    // {
    //     dd(request()->all()); // 檢查 cover_image 是否在請求中
    //     return parent::store();
    // }

    // public function update()
    // {
    //     dd(request()->all());
    //     return parent::update();
    // }
}
