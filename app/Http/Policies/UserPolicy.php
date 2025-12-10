<?php

namespace NinjaTables\App\Http\Policies;

use NinjaTables\Framework\Http\Request\Request;
use NinjaTables\Framework\Foundation\Policy;

class UserPolicy extends Policy
{
    /**
     * Check user permission for any method
     *
     * @param NinjaTables\Framework\Http\Request\Request $request
     *
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return current_user_can(ninja_table_admin_role());
    }
}
