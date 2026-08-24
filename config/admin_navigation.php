<?php

return [
    [
        'label' => 'Overview',
        'items' => [
            ['label' => 'Dashboard', 'description' => 'Admin workspace overview', 'icon' => 'bi-grid-1x2', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard']],
            ['label' => 'Registered Users', 'description' => 'View registered website users', 'icon' => 'bi-people', 'route' => 'admin.users.index', 'active' => ['admin.users.*']],
        ],
    ],
    [
        'label' => 'Trademarks',
        'items' => [
            ['label' => 'All Applications', 'description' => 'View every trademark application', 'icon' => 'bi-journal-text', 'route' => 'admin.all-applications', 'active' => ['admin.all-applications', 'admin.view-application', 'admin.review-application']],
            ['label' => 'Pending Review', 'description' => 'Review new and pending submissions', 'icon' => 'bi-hourglass-split', 'route' => 'admin.applications', 'active' => ['admin.applications']],
            ['label' => 'Recovery Cases', 'description' => 'Manage stuck trademark matters', 'icon' => 'bi-arrow-repeat', 'route' => 'admin.stuck-trademark.index', 'active' => ['admin.stuck-trademark.*', 'admin.trademark-execution.*']],
            ['label' => 'Pricing Settings', 'description' => 'Set trademark service prices', 'icon' => 'bi-currency-rupee', 'route' => 'admin.trademark-pricing.edit', 'active' => ['admin.trademark-pricing.*']],
        ],
    ],
    [
        'label' => 'Opposition & Objections',
        'items' => [
            ['label' => 'Defence Cases', 'description' => 'Defend trademarks under opposition', 'icon' => 'bi-shield-check', 'route' => 'admin.trademark-opposition.index', 'active' => ['admin.trademark-opposition.index', 'admin.trademark-opposition.show']],
            ['label' => 'Oppose Cases', 'description' => 'Manage trademark opposition filings', 'icon' => 'bi-bank', 'route' => 'admin.trademark-opposition.oppose.index', 'active' => ['admin.trademark-opposition.oppose.*']],
            ['label' => 'Objection Replies', 'description' => 'Handle examination report replies', 'icon' => 'bi-file-earmark-check', 'route' => 'admin.examination-reply.index', 'active' => ['admin.examination-reply.*']],
        ],
    ],
    [
        'label' => 'Website',
        'items' => [
            ['label' => 'Blogs', 'description' => 'Publish and manage articles', 'icon' => 'bi-newspaper', 'route' => 'admin.blogs.index', 'active' => ['admin.blogs.*']],
            ['label' => 'FAQs', 'description' => 'Manage frequently asked questions', 'icon' => 'bi-question-circle', 'route' => 'admin.faqs.index', 'active' => ['admin.faqs.*']],
            ['label' => 'Reviews', 'description' => 'Manage homepage customer reviews', 'icon' => 'bi-star', 'route' => 'admin.reviews.index', 'active' => ['admin.reviews.*']],
            ['label' => 'Job Roles', 'description' => 'Publish and manage open positions', 'icon' => 'bi-briefcase', 'route' => 'admin.career-jobs.index', 'active' => ['admin.career-jobs.*']],
            ['label' => 'Discount Coupons', 'description' => 'Manage promotional discount codes', 'icon' => 'bi-ticket-perforated', 'route' => 'admin.discount-coupons.index', 'active' => ['admin.discount-coupons.*']],
        ],
    ],
    [
        'label' => 'CMS',
        'items' => [
            ['label' => 'Legal Pages', 'description' => 'Edit policies and disclaimer popup', 'icon' => 'bi-file-earmark-richtext', 'route' => 'admin.cms-pages.index', 'active' => ['admin.cms-pages.*']],
        ],
    ],
    [
        'label' => 'Inbox',
        'items' => [
            ['label' => 'Contact Messages', 'description' => 'Read and resolve website enquiries', 'icon' => 'bi-envelope', 'route' => 'admin.contact-messages.index', 'active' => ['admin.contact-messages.*']],
            ['label' => 'Career Applications', 'description' => 'Review submitted job applications', 'icon' => 'bi-person-workspace', 'route' => 'admin.career-applications.index', 'active' => ['admin.career-applications.*']],
        ],
    ],
];
