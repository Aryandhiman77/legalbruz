@once
    <style>
        .government-fee-notice {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            margin: .85rem 0;
            padding: .75rem .85rem;
            border: 1px solid #f1cf79;
            border-radius: 9px;
            background: #fff9e8;
            color: #69490d;
            font-size: .85rem;
            font-weight: 650;
            line-height: 1.45;
        }

        .government-fee-notice__icon {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #ffedb7;
            color: #946200;
            font-size: .78rem;
            font-weight: 900;
        }

        .government-fee-notice p { margin: 0 !important; }
        .government-fee-notice strong { color: #5b3d08; }
    </style>
@endonce

<div class="government-fee-notice" role="note">
    <span class="government-fee-notice__icon" aria-hidden="true">i</span>
    <p><strong>Government fees are not included.</strong> Any applicable Government or Registry fee must be paid separately.</p>
</div>
