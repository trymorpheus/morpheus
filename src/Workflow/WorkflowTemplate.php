<?php

namespace Morpheus\Workflow;

class WorkflowTemplate
{
    public static function orderManagement(): array
    {
        return [
            'field' => 'status',
            'states' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
            'transitions' => [
                'process' => [
                    'from' => 'pending',
                    'to' => 'processing',
                    'label' => 'Process Order',
                    'permissions' => ['admin', 'manager'],
                    'color' => '#3b82f6'
                ],
                'ship' => [
                    'from' => 'processing',
                    'to' => 'shipped',
                    'label' => 'Ship Order',
                    'permissions' => ['admin', 'warehouse'],
                    'color' => '#8b5cf6'
                ],
                'deliver' => [
                    'from' => 'shipped',
                    'to' => 'delivered',
                    'label' => 'Mark as Delivered',
                    'permissions' => ['admin', 'delivery'],
                    'color' => '#10b981'
                ],
                'cancel' => [
                    'from' => ['pending', 'processing'],
                    'to' => 'cancelled',
                    'label' => 'Cancel Order',
                    'permissions' => ['admin', 'customer'],
                    'color' => '#ef4444'
                ]
            ],
            'state_labels' => [
                'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
                'processing' => ['label' => 'Processing', 'color' => '#3b82f6'],
                'shipped' => ['label' => 'Shipped', 'color' => '#8b5cf6'],
                'delivered' => ['label' => 'Delivered', 'color' => '#10b981'],
                'cancelled' => ['label' => 'Cancelled', 'color' => '#ef4444']
            ],
            'history' => true
        ];
    }
    
    public static function ticketSupport(): array
    {
        return [
            'field' => 'status',
            'states' => ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'],
            'transitions' => [
                'start' => [
                    'from' => 'open',
                    'to' => 'in_progress',
                    'label' => 'Start Working',
                    'permissions' => ['support', 'admin']
                ],
                'wait' => [
                    'from' => 'in_progress',
                    'to' => 'waiting_customer',
                    'label' => 'Wait for Customer'
                ],
                'resume' => [
                    'from' => 'waiting_customer',
                    'to' => 'in_progress',
                    'label' => 'Resume'
                ],
                'resolve' => [
                    'from' => ['in_progress', 'waiting_customer'],
                    'to' => 'resolved',
                    'label' => 'Mark as Resolved',
                    'permissions' => ['support', 'admin']
                ],
                'close' => [
                    'from' => 'resolved',
                    'to' => 'closed',
                    'label' => 'Close Ticket',
                    'permissions' => ['admin']
                ]
            ],
            'history' => true
        ];
    }
    
    public static function approvalProcess(): array
    {
        return [
            'field' => 'approval_status',
            'states' => ['draft', 'pending_review', 'approved', 'rejected'],
            'transitions' => [
                'submit' => [
                    'from' => 'draft',
                    'to' => 'pending_review',
                    'label' => 'Submit for Review'
                ],
                'approve' => [
                    'from' => 'pending_review',
                    'to' => 'approved',
                    'label' => 'Approve',
                    'permissions' => ['manager', 'admin'],
                    'color' => '#10b981'
                ],
                'reject' => [
                    'from' => 'pending_review',
                    'to' => 'rejected',
                    'label' => 'Reject',
                    'permissions' => ['manager', 'admin'],
                    'color' => '#ef4444'
                ],
                'revise' => [
                    'from' => 'rejected',
                    'to' => 'draft',
                    'label' => 'Revise'
                ]
            ],
            'history' => true
        ];
    }
    
    public static function contentPublishing(): array
    {
        return [
            'field' => 'status',
            'states' => ['draft', 'review', 'scheduled', 'published', 'archived'],
            'transitions' => [
                'review' => [
                    'from' => 'draft',
                    'to' => 'review',
                    'label' => 'Send to Review'
                ],
                'schedule' => [
                    'from' => 'review',
                    'to' => 'scheduled',
                    'label' => 'Schedule',
                    'permissions' => ['editor', 'admin']
                ],
                'publish' => [
                    'from' => ['review', 'scheduled'],
                    'to' => 'published',
                    'label' => 'Publish Now',
                    'permissions' => ['editor', 'admin'],
                    'color' => '#10b981'
                ],
                'archive' => [
                    'from' => 'published',
                    'to' => 'archived',
                    'label' => 'Archive',
                    'permissions' => ['admin']
                ]
            ],
            'history' => true
        ];
    }
}
