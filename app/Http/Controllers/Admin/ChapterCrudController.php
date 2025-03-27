<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ChapterRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Auth;

/**
 * Class ChapterCrudController
 * @package App\Http\Controllers\Admin
 */
class ChapterCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * 基本設定
     */
    public function setup()
    {
        // 指定 Model
        CRUD::setModel(\App\Models\Chapter::class);

        // 後台路由：/admin/chapter
        CRUD::setRoute(config('backpack.base.route_prefix') . '/chapter');

        // Entity 名稱（單/複數）
        CRUD::setEntityNameStrings('chapter', 'chapters');

        // 預載 course 關聯，避免 N+1
        $this->crud->with('course');

        // 若需要「老師只能管理自己課程的章節」
        if (Auth::check() && Auth::user()->role === 'teacher') {
            // whereHas('course', ...) → 僅找 course.teacher_id = Auth::id()
            $this->crud->addClause('whereHas', 'course', function ($query) {
                $query->where('teacher_id', Auth::id());
            });
        }
    }

    /**
     * 列表頁面
     */
    protected function setupListOperation()
    {
        // 自動根據資料表欄位生成 columns
        CRUD::setFromDb();

        // 將 course_id 顯示為課程標題
        CRUD::modifyColumn('course_id', [
            'label' => 'Course',
            'type'  => 'select',
            'entity' => 'course',       // 關聯方法 course()
            'model'  => "App\\Models\\Course",
            'attribute' => 'title',     // 顯示課程的 title
        ]);

        // 可額外調整欄位
        CRUD::modifyColumn('title', [
            'label' => '章節標題',
            'type'  => 'text',
        ]);

        CRUD::modifyColumn('video_url', [
            'label' => '影片連結',
            'type'  => 'text',
        ]);

        CRUD::modifyColumn('sort_order', [
            'label' => '排序',
            'type'  => 'number',
        ]);
    }

    /**
     * 新增頁面
     */
    protected function setupCreateOperation()
    {
        // 若有生成 ChapterRequest，可啟用驗證
        CRUD::setValidation(ChapterRequest::class);

        // 自動從資料表載入 fields
        CRUD::setFromDb();

        // course_id → 選擇課程
        CRUD::modifyField('course_id', [
            'label'     => 'Course',
            'type'      => 'select',
            'entity'    => 'course',      // Chapter model 裡的 course() 方法
            'model'     => "App\\Models\\Course",
            'attribute' => 'title',
        ]);

        // title → 章節標題
        CRUD::modifyField('title', [
            'label' => '章節標題',
            'type'  => 'text',
        ]);

        // video_url → 影片連結 (YouTube / Vimeo)
        CRUD::modifyField('video_url', [
            'label' => '影片連結',
            'type'  => 'text',
        ]);

        // sort_order → 排序
        CRUD::modifyField('sort_order', [
            'label' => '排序',
            'type'  => 'number',
        ]);

        /**
         * 如果您想要「直接上傳小影片檔」，可加一個 video_file 欄位：
         *
         *  CRUD::addField([
         *      'name'  => 'video_file',
         *      'label' => '上傳影片',
         *      'type'  => 'upload',
         *      // Backpack 6.8 新上傳器用法：
         *      'upload' => true,
         *      'disk' => 'public',
         *      'destination_path' => 'uploads/chapters/videos',
         *  ]);
         *
         *  然後在 chapters 表加一個 video_file 欄位 (string)，存相對路徑。
         */
    }

    /**
     * 更新頁面
     */
    protected function setupUpdateOperation()
    {
        // 直接重用 create 設定
        $this->setupCreateOperation();
    }
}
