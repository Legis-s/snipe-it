<?php

namespace App\Models\Traits;

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Location;

trait LogsAssetWorkflows
{
    public function logSell(mixed $note, mixed $target, mixed $action_date = null, array $originalValues = []): Actionlog
    {

        $log = new Actionlog;

        $fields_array = [];

        $log = $this->determineLogItemType($log);
        if (auth()->user()) {
            $log->created_by = auth()->id();
        }

        if (! isset($target)) {
            throw new \Exception('All sell logs require a target.');
        }

        if (! isset($target->id)) {
            throw new \Exception('That target seems invalid (no target ID available).');
        }

        $log->target_type = get_class($target);
        $log->target_id = $target->id;

        // Figure out what the target is
        if ($log->target_type == Location::class) {
            $log->location_id = $target->id;
        } elseif ($log->target_type == Asset::class) {
            $log->location_id = $target->location_id;
        } else {
            $log->location_id = $target->location_id;
        }

        if (static::class == Asset::class) {
            if ($asset = Asset::find($log->item_id)) {

                // add the custom fields that were changed
                if ($asset->model->fieldset) {
                    $fields_array = [];
                    foreach ($asset->model->fieldset->fields as $field) {
                        if ($field->display_checkout == 1) {
                            $fields_array[$field->db_column] = $asset->{$field->db_column};
                        }
                    }
                }
            }
        }

        $log->note = $note;
        $log->action_date = $action_date;

        $changed = [];
        $array_to_flip = array_keys($fields_array);
        $array_to_flip = array_merge($array_to_flip, ['name', 'status_id', 'location_id', 'expected_checkin']);
        $originalValues = array_intersect_key($originalValues, array_flip($array_to_flip));

        foreach ($originalValues as $key => $value) {
            // TODO - action_date isn't a valid attribute of any first-class object, so we might want to remove this?
            if ($key == 'action_date' && $value != $action_date) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = is_string($action_date) ? $action_date : $action_date->format('Y-m-d H:i:s');
            } elseif (array_key_exists($key, $this->getAttributes()) && $value != $this->getAttributes()[$key]) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = $this->getAttributes()[$key];
            }
            // NOTE - if the attribute exists in $originalValues, but *not* in ->getAttributes(), it isn't added to $changed
        }

        if (! empty($changed)) {
            $log->log_meta = json_encode($changed);
        }

        $log->logaction('sell');

        return $log;
    }

    public function logRent(mixed $note, mixed $target, mixed $action_date = null, array $originalValues = []): Actionlog
    {
        $log = new Actionlog;

        $fields_array = [];

        $log = $this->determineLogItemType($log);
        if (auth()->user()) {
            $log->created_by = auth()->id();
        }

        if (! isset($target)) {
            throw new \Exception('All checkout logs require a target.');
        }

        if (! isset($target->id)) {
            throw new \Exception('That target seems invalid (no target ID available).');
        }

        $log->target_type = get_class($target);
        $log->target_id = $target->id;

        // Figure out what the target is
        if ($log->target_type == Location::class) {
            $log->location_id = $target->id;
        } elseif ($log->target_type == Asset::class) {
            $log->location_id = $target->location_id;
        } else {
            $log->location_id = $target->location_id;
        }

        if (static::class == Asset::class) {
            if ($asset = Asset::find($log->item_id)) {

                // add the custom fields that were changed
                if ($asset->model->fieldset) {
                    $fields_array = [];
                    foreach ($asset->model->fieldset->fields as $field) {
                        if ($field->display_checkout == 1) {
                            $fields_array[$field->db_column] = $asset->{$field->db_column};
                        }
                    }
                }
            }
        }

        $log->note = $note;
        $log->action_date = $action_date;

        $changed = [];
        $array_to_flip = array_keys($fields_array);
        $array_to_flip = array_merge($array_to_flip, ['name', 'status_id', 'location_id', 'expected_checkin']);
        $originalValues = array_intersect_key($originalValues, array_flip($array_to_flip));

        foreach ($originalValues as $key => $value) {
            // TODO - action_date isn't a valid attribute of any first-class object, so we might want to remove this?
            if ($key == 'action_date' && $value != $action_date) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = is_string($action_date) ? $action_date : $action_date->format('Y-m-d H:i:s');
            } elseif (array_key_exists($key, $this->getAttributes()) && $value != $this->getAttributes()[$key]) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = $this->getAttributes()[$key];
            }
            // NOTE - if the attribute exists in $originalValues, but *not* in ->getAttributes(), it isn't added to $changed
        }

        if (! empty($changed)) {
            $log->log_meta = json_encode($changed);
        }

        $log->logaction(ActionType::Rent);

        return $log;
    }

    public function logTag(mixed $note, array $originalValues = []): Actionlog
    {
        $log = new Actionlog;
        $fields_array = [];

        $log = $this->determineLogItemType($log);

        if (auth()->user()) {
            $log->created_by = auth()->id();
        }
        $action_date = date('Y-m-d H:i:s');
        $log->note = $note;
        $log->action_date = $action_date;

        if (static::class == Asset::class) {
            if ($asset = Asset::find($log->item_id)) {

                // add the custom fields that were changed
                if ($asset->model->fieldset) {
                    $fields_array = [];
                    foreach ($asset->model->fieldset->fields as $field) {
                        if ($field->display_checkout == 1) {
                            $fields_array[$field->db_column] = $asset->{$field->db_column};
                        }
                    }
                }
            }
        }

        $changed = [];
        $array_to_flip = array_keys($fields_array);
        $array_to_flip = array_merge($array_to_flip, ['name', 'status_id', 'location_id', 'expected_checkin']);
        $originalValues = array_intersect_key($originalValues, array_flip($array_to_flip));

        foreach ($originalValues as $key => $value) {
            // TODO - action_date isn't a valid attribute of any first-class object, so we might want to remove this?
            if ($key == 'action_date' && $value != $action_date) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = is_string($action_date) ? $action_date : $action_date->format('Y-m-d H:i:s');
            } elseif (array_key_exists($key, $this->getAttributes()) && $value != $this->getAttributes()[$key]) {
                $changed[$key]['old'] = $value;
                $changed[$key]['new'] = $this->getAttributes()[$key];
            }
            // NOTE - if the attribute exists in $originalValues, but *not* in ->getAttributes(), it isn't added to $changed
        }

        if (! empty($changed)) {
            $log->log_meta = json_encode($changed);
        }

        $log->logaction('tag');

        return $log;
    }
}
