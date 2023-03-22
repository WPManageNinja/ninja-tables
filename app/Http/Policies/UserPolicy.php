<?php

namespace NinjaTables\App\Http\Policies;

use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Foundation\Policy;

class UserPolicy extends Policy
{
    /**
     * Check user permission for any method
     *
     * @param NinjaTables\Framework\Request\Request $request
     *
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return current_user_can(ninja_table_admin_role());
    }

    public function defaultExport(Request $request)
    {
//       TODO: Check if the user has logged in; if so, return true; otherwise, return false.
        return true;
    }

    public function dragAndDropExport(Request $request)
    {
//       TODO: Check if the user has logged in; if so, return true; otherwise, return false.
        return true;
    }
}
