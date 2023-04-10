<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;
use  NinjaTables\App\Modules\DataProviders\FluentFormProvider;
use NinjaTables\Framework\Support\Arr;


class FluentFormsController extends Controller
{
    public function index(Request $request)
    {
        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        if (function_exists('wpFluentForm')) {
            $forms = wpFluent()->table('fluentform_forms')->select(array('id', 'title'))->get();

            return $this->sendSuccess([
                'data' => $forms
            ], 200);
        }
    }

    public function getFormsFields(Request $request, $id)
    {
        $id = intval($id);

        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        $labels = (new FluentFormProvider())->getFields($id);

        return $this->sendSuccess([
            'data' => $labels
        ], 200);
    }

    public function store(Request $request)
    {
        if ( ! current_user_can(ninja_table_admin_role())) {
            return;
        }

        $messages = array();
        $tableId  = intval(Arr::get($request->all(), 'table_Id'));
        $formId   = intval(Arr::get($request->form, 'id'));

        if ( ! $tableId) {
            // Validate Title
            if (empty(Arr::get($request->all(), 'post_title'))) {
                $messages['title'] = __('The title field is required.', 'ninja-tables');
            }
        }

        // Validate Columns
        $fields = ninja_tables_sanitize_array(Arr::get($request->form, 'fields', array()));

        if ( ! $fields) {
            $messages['fields'] = __('No fields were selected.', 'ninja-tables');
        }

        // If Validation failed
        if (array_filter($messages)) {
            return $this->sendError([
                'data' => [
                    'message' => $messages
                ]
            ], 422);
        }

        $form = Arr::get($request->all(), 'form');

        $tableId = (new FluentFormProvider())->saveTable($form, $fields, $tableId, $formId);

        return $this->sendSuccess([
            'data' => [
                'table_id' => $tableId,
                'form_id'  => $formId
            ]
        ], 200);
    }

}