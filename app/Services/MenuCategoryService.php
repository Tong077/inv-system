<?php
namespace App\Services;
use App\Models\MenuCategory;

class MenuCategoryService
{
    public function getAllMenuCategories()
    {
        $all_menu_categories = MenuCategory::all();
        return $all_menu_categories;
    }
    public function getMenuCategoryById($id)
    {
        $menu_category = MenuCategory::find($id);
        return $menu_category;
    }
    public function createMenuCategory(array $data)
    {
        $menu_category = MenuCategory::create($data);
        return $menu_category;
    }
    public function updateMenuCategory($id, array $data)
    {
        $menu_category = MenuCategory::find($id);
        if ($menu_category) {
            $menu_category->update($data);
            return $menu_category;
        }
        return null;
    }
    public function deleteMenuCategory($id)
    {
        $menu_category = MenuCategory::find($id);
        if ($menu_category) {
            $menu_category->delete();
            return true;
        }
        return false;
    }
    
}