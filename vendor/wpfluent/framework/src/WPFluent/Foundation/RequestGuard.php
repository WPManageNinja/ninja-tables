<?php

namespace NinjaTables\Framework\Foundation;

use NinjaTables\Framework\Foundation\App;
use NinjaTables\Framework\Validator\ValidationException;

abstract class RequestGuard
{
    /**
     * Retrive the validation rules
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Retrive the validation messages set by the developer
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Allow the developer tinker with data before the validation.
     * @return array
     */
    public function beforeValidation()
    {
        return [];
    }

    /**
     * Allow the developer tinker with data after the validation.
     * @return array
     */
    public function afterValidation($validator)
    {
        return [];
    }

    /**
     * Validate the request.
     * 
     * @param  array $rules Optional
     * @param  array $messages Optional
     * @return array Request Data
     * @throws NinjaTables\Framework\Validator\ValidationException
     */
    public function validate($rules = [], $messages = [])
    {
        try {
            return App::make('request')->validate(
                $rules ?: (array) $this->rules(),
                $messages ?: (array) $this->messages()
            );
        } catch (ValidationException $e) {
            
            $validator = App::make('validator');
            
            $validator->addError($e->errors());

            $after = $this->afterValidation($validator);

            if (!($errors = $validator->errors())) {
                return;
            }

            $e = new ValidationException(
                'Unprocessable Entity!', 422, null, $e->errors()
            );

            if ($this->shouldThrowException()) {
                throw $e;
            } else {
                App::make()->doCustomAction('handle_exception', $e);
            }
        }
    }

    /**
     * Check if exceptions should be thrown.
     * 
     * @return bool
     */
    protected function shouldThrowException()
    {
        $isTrue = $this->isRest();
        $isTrue = $isTrue || str_contains('test', App::make()->env());
        $isTrue = $isTrue || str_contains(strtolower(php_sapi_name()), 'cli');
        return $isTrue;
    }

    /**
     * Handles validation including before and after calls
     *
     * @throws NinjaTables\Framework\Validator\ValidationException
     * @return null
     */
    public static function applyValidation()
    {
        $instance = new static;

        $request = App::getInstance('request');

        $request->merge($instance->beforeValidation());

        $instance->validate();

        $request->merge($instance->afterValidation());
    }

    /**
     * Get an input element from the request.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Handle the dynamic method calls
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array(
            [App::make('request'), $method], $params
        );
    }
}
