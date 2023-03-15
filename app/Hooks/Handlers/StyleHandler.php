<?php

namespace NinjaTables\App\Hooks\Handlers;

class StyleHandler
{
    public function adminMenuStyle()
    {
        ?>
        <style>
            #adminmenu #toplevel_page_ninja_tables li.ninja_tables_help:before {
                background: #b4b9be;
                content: "";
                display: block;
                height: 1px;
                margin: 5px auto 0;
                width: calc(100% - 24px);
                opacity: .4;
            }
        </style>
        <?php
    }
}
