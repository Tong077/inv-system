<?php
namespace App\Services;

use App\Models\Unit;

class UnitService
{
    public function getUnit()
    {
        $unit = Unit::all();
        return $unit;
    }
    public function getUnitById($id)
    {
        $unit = Unit::find($id);
        return $unit;
    }
    public function createUnit(array $data)
    {
        $unit = Unit::create($data);
        return $unit;
    }
    public function updateUnit(array $data)
    {
        $unit = $this->getUnitById($data['id']);
        if ($unit) {
            $unit->update($data);
            return $unit;
        }
        return null;
    }
    public function deleteUnit($id)
    {
        $unit = $this->getUnitById($id);
        if ($unit) {
            $unit->delete();
            return true;
        }
        return false;
    }
}