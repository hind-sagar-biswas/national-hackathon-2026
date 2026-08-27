<?php

namespace App\Enums;

enum Permission: string
{
    case VIEW_USERS = 'view-users';
    case CREATE_USERS = 'create-users';
    case UPDATE_USERS = 'update-users';
    case DELETE_USERS = 'delete-users';
    case TOGGLE_USERS = 'toggle-users';

    case DELETE_ACCOUNT = 'delete-account';
}
