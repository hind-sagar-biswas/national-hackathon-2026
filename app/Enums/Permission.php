<?php

namespace App\Enums;

enum Permission: string
{
    // User & Profile Management
    case VIEW_USERS = 'view-users';
    case VIEW_USER = 'view-user';
    case CREATE_USERS = 'create-users';
    case UPDATE_USERS = 'update-users';
    case DELETE_USERS = 'delete-users';
    case TOGGLE_USERS = 'toggle-users';
    case DELETE_ACCOUNT = 'delete-account';

    // Dashboards
    case VIEW_ADMIN_DASHBOARD = 'view-admin-dashboard';
    case VIEW_USER_DASHBOARD = 'view-user-dashboard';

    // Transfers
    case VIEW_TRANSFERS = 'view-transfers';
    case CREATE_TRANSFERS = 'create-transfers';

    // Money Requests (P2P Invoicing)
    case VIEW_MONEY_REQUESTS = 'view-money-requests';
    case CREATE_MONEY_REQUESTS = 'create-money-requests';
    case APPROVE_MONEY_REQUESTS = 'approve-money-requests';
    case REJECT_MONEY_REQUESTS = 'reject-money-requests';
    case DELETE_MONEY_REQUESTS = 'delete-money-requests';

    // Peer-to-Peer Loans
    case VIEW_LOANS = 'view-loans';
    case VIEW_LOAN = 'view-loan';
    case CREATE_LOANS = 'create-loans';
    case REPAY_LOANS = 'repay-loans';
    case WAIVE_LOANS = 'waive-loans';

    // Deposits
    case VIEW_DEPOSITS = 'view-deposits';
    case CREATE_DEPOSITS = 'create-deposits';
    case CONFIRM_DEPOSITS = 'confirm-deposits';
    case REJECT_DEPOSITS = 'reject-deposits';

    // Transactions Explorer
    case VIEW_TRANSACTIONS = 'view-transactions';
    case VIEW_TRANSACTION = 'view-transaction';
    case VIEW_ALL_TRANSACTIONS = 'view-all-transactions';

    // Compliance & Holds
    case VIEW_HOLDS = 'view-holds';
    case RELEASE_HOLDS = 'release-holds';
    case CAPTURE_HOLDS = 'capture-holds';

    // General Ledger & Reconciliation
    case VIEW_RECONCILIATION = 'view-reconciliation';
    case RUN_RECONCILIATION_AUDIT = 'run-reconciliation-audit';
    case RUN_RECONCILIATION_ROLLUP = 'run-reconciliation-rollup';
}
