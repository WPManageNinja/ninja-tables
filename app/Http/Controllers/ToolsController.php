<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\App\Models\Post;
use NinjaTables\Framework\Request\Request;

class ToolsController extends Controller
{
    public function getDefaultSettings(Request $request)
    {
        $settings = getDefaultNinjaTableSettings();

        return $this->sendSuccess([
            'data' => [
                'default_settings' => $settings
            ]
        ], 200);
    }

    public function saveDefaultSettings(Request $request)
    {
        $settings = ninjaTableNormalize(wp_unslash($request->default_settings));
        update_option('_ninja_table_default_appearance_settings', $settings);

        return $this->sendSuccess([
            'data' => [
                'message' => __('Settings successfully updated', 'ninja-tables')
            ]
        ], 200);
    }

    // Permission & licence's code will be goes here

    public function getGlobalSettings(Request $request)
    {
        $suppressError = get_option('_ninja_suppress_error');
        if ( ! $suppressError) {
            $suppressError = 'no';
        }

        return $this->sendSuccess([
            'data' => [
                'ninja_suppress_error' => $suppressError
            ]
        ], 200);
    }

    public function updateGlobalSettings(Request $request)
    {
        $errorHandling = sanitize_text_field($request->suppress_error);
        update_option('_ninja_suppress_error', $errorHandling, true);

        return $this->sendSuccess([
            'data' => [
                'message' => __('Settings successfully updated', 'ninja-tables')
            ]
        ], 200);
    }

    public function clearTableCache(Request $request)
    {
        $posts = Post::where('post_type', 'ninja-table')->get();

        ninja_table_clear_all_cache($posts);

        return $this->sendSuccess([
            'data' => [
                'posts'   => $posts,
                'message' => __('All Cache successfully cleared', 'ninja_tables')
            ]
        ], 200);
    }

}