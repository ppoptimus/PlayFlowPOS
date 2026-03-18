<style>
    .masseuse-page .hero-card,
    .masseuse-page .staff-card,
    .masseuse-page .attendance-card,
    .masseuse-page .form-card {
        border: 1px solid rgba(31, 115, 224, 0.12);
        border-radius: 1.3rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 250, 255, 0.96));
        box-shadow: 0 16px 32px rgba(17, 81, 146, 0.08);
    }

    .masseuse-page .hero-card {
        background: linear-gradient(140deg, rgba(34, 112, 193, 0.98), rgba(20, 184, 154, 0.95));
        color: #ffffff;
        overflow: hidden;
        position: relative;
    }

    .masseuse-page .hero-card::after {
        content: '';
        position: absolute;
        inset: auto -8% -45% auto;
        width: 14rem;
        height: 14rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.08);
    }

    .masseuse-page .hero-title {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .masseuse-page .hero-subtitle {
        max-width: 42rem;
        color: rgba(255, 255, 255, 0.82);
    }

    .masseuse-page .hero-metric {
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.16);
        padding: 0.85rem 0.95rem;
        min-height: 100%;
    }

    .masseuse-page .hero-metric-label {
        display: block;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 0.2rem;
    }

    .masseuse-page .hero-metric-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ffffff;
    }

    .masseuse-page .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #234262;
        margin-bottom: 0;
    }

    .masseuse-page .section-subtitle {
        font-size: 0.84rem;
        color: #68809a;
    }

    .masseuse-page .helper-text {
        font-size: 0.76rem;
        color: #6e8193;
    }

    .masseuse-page .section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .masseuse-page .section-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        flex-shrink: 0;
        min-height: 2.5rem;
        padding: 0.4rem 0.5rem 0.4rem 0.45rem;
        border-radius: 0.95rem;
        font-size: 0.84rem;
        white-space: nowrap;
        border: 1px solid rgba(31, 115, 224, 0.14);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(235, 246, 255, 0.95));
        color: #1d67bd;
        box-shadow: 0 12px 24px rgba(21, 101, 181, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .masseuse-page .section-action-btn:hover,
    .masseuse-page .section-action-btn:focus {
        color: #17579f;
        background: linear-gradient(135deg, rgba(255, 255, 255, 1), rgba(227, 243, 255, 0.98));
        box-shadow: 0 14px 26px rgba(21, 101, 181, 0.16);
        transform: translateY(-1px);
    }

    .masseuse-page .section-action-icon {
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        color: #ffffff;
        font-size: 0.82rem;
        box-shadow: 0 8px 16px rgba(32, 118, 204, 0.22);
    }

    .masseuse-page .section-action-label {
        padding-right: 0.15rem;
    }

    .masseuse-page .staff-card,
    .masseuse-page .attendance-card,
    .masseuse-page .form-card {
        padding: 1rem;
    }

    .masseuse-page .staff-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .masseuse-page .staff-card.is-off-duty {
        background: linear-gradient(180deg, rgba(243, 246, 249, 0.98), rgba(235, 240, 245, 0.96));
        border-color: rgba(141, 153, 168, 0.2);
    }

    .masseuse-page .staff-toolbar,
    .masseuse-page .staff-head {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .masseuse-page .staff-toolbar {
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.9rem;
    }

    .masseuse-page .staff-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.6rem;
        flex-shrink: 0;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .masseuse-page .staff-period-switches {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .masseuse-page .period-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2rem;
        padding: 0.25rem 0.7rem;
        border-radius: 0.8rem;
        border: 1px solid rgba(31, 115, 224, 0.14);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(238, 247, 255, 0.95));
        color: #1d67bd;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(31, 115, 224, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease, color 0.2s ease, background 0.2s ease;
    }

    .masseuse-page .period-chip:hover,
    .masseuse-page .period-chip:focus {
        color: #ffffff;
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        box-shadow: 0 10px 20px rgba(31, 115, 224, 0.18);
        transform: translateY(-1px);
    }

    .masseuse-page .staff-avatar {
        width: 58px;
        height: 58px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(45, 143, 240, 0.26);
        background: #ffffff;
        flex-shrink: 0;
    }

    .masseuse-page .staff-name {
        font-size: 1.08rem;
        font-weight: 700;
        line-height: 1.1;
        color: #234262;
        margin-bottom: 0.16rem;
    }

    .masseuse-page .staff-id {
        font-size: 0.77rem;
        color: #6f8498;
    }

    .masseuse-page .status-pill,
    .masseuse-page .soft-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.32rem 0.68rem;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .masseuse-page .status-pill {
        background: rgba(31, 115, 224, 0.1);
        color: #1d67bd;
    }

    .masseuse-page .soft-badge {
        background: rgba(31, 115, 224, 0.08);
        color: #275f9c;
        font-size: 0.74rem;
    }

    .masseuse-page .summary-panels {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .masseuse-page .summary-panel {
        padding: 0.9rem 0.95rem;
        border-radius: 1.05rem;
        background: linear-gradient(180deg, rgba(245, 250, 255, 0.98), rgba(236, 245, 255, 0.96));
        border: 1px solid rgba(31, 115, 224, 0.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    .masseuse-page .summary-panel.is-month {
        background: linear-gradient(180deg, rgba(242, 251, 248, 0.98), rgba(232, 247, 243, 0.96));
        border-color: rgba(20, 184, 154, 0.12);
    }

    .masseuse-page .summary-panel-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #234262;
        margin-bottom: 0.65rem;
    }

    .masseuse-page .summary-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.55rem;
    }

    .masseuse-page .summary-metrics.is-two-columns {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        row-gap: 0.75rem;
    }

    .masseuse-page .summary-metric {
        min-width: 0;
    }

    .masseuse-page .summary-metric.is-full {
        grid-column: 1 / -1;
    }

    .masseuse-page .summary-label {
        display: block;
        font-size: 0.74rem;
        color: #6d8398;
        margin-bottom: 0.18rem;
    }

    .masseuse-page .summary-value {
        font-size: 1.02rem;
        font-weight: 700;
        color: #234262;
        line-height: 1.2;
        word-break: break-word;
    }

    .masseuse-page .status-pill.is-success {
        background: rgba(20, 184, 154, 0.12);
        color: #108974;
    }

    .masseuse-page .status-pill.is-warning {
        background: rgba(242, 179, 64, 0.16);
        color: #a56a00;
    }

    .masseuse-page .status-pill.is-muted {
        background: rgba(109, 124, 142, 0.12);
        color: #5f7182;
    }

    .masseuse-page .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.55rem;
        margin-top: 0.95rem;
    }

    .masseuse-page .stat-box {
        padding: 0.72rem 0.75rem;
        border-radius: 0.95rem;
        background: rgba(239, 247, 255, 0.92);
        border: 1px solid rgba(31, 115, 224, 0.08);
    }

    .masseuse-page .stat-label {
        display: block;
        font-size: 0.72rem;
        color: #6d8398;
        margin-bottom: 0.12rem;
    }

    .masseuse-page .stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: #234262;
    }

    .masseuse-page .queue-box {
        margin-top: 0.95rem;
        border-radius: 0.95rem;
        border: 1px solid rgba(31, 115, 224, 0.08);
        background: rgba(250, 252, 255, 0.95);
        padding: 0.78rem 0.82rem;
        min-height: 5.35rem;
    }

    .masseuse-page .queue-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: #234262;
        margin-bottom: 0.45rem;
    }

    .masseuse-page .queue-meta {
        display: block;
        line-height: 1.3;
        color: #5d738a;
        font-size: 0.8rem;
    }

    .masseuse-page .queue-time {
        color: #1d67bd;
        font-weight: 700;
    }

    .masseuse-page .empty-state {
        text-align: center;
        color: #70859a;
        padding: 2.2rem 1rem;
        border-radius: 1rem;
        border: 1px dashed rgba(31, 115, 224, 0.18);
        background: rgba(255, 255, 255, 0.78);
    }

    .masseuse-page .attendance-table th {
        color: #1e5f9d;
        background: linear-gradient(180deg, #eef6ff 0%, #e8f3ff 100%);
        border-bottom-color: rgba(31, 115, 224, 0.15);
    }

    .masseuse-page .attendance-table td,
    .masseuse-page .attendance-table th {
        vertical-align: middle;
    }

    .masseuse-page .attendance-col {
        width: 1%;
        white-space: nowrap;
        text-align: center;
    }

    .masseuse-page .attendance-row-off {
        color: #8d99a8;
    }

    .masseuse-page .attendance-row-off td {
        background-color: rgba(239, 243, 246, 0.65) !important;
    }

    .masseuse-page .toggle {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .masseuse-page .toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .masseuse-page .toggle-track {
        width: 2.8rem;
        height: 1.6rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #c4d3e2, #d7e1ea);
        position: relative;
        box-shadow: inset 0 1px 3px rgba(29, 61, 94, 0.16);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .masseuse-page .toggle-thumb {
        position: absolute;
        top: 0.16rem;
        left: 0.18rem;
        width: 1.28rem;
        height: 1.28rem;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 3px 8px rgba(20, 55, 90, 0.18);
        transition: transform 0.2s ease;
    }

    .masseuse-page .toggle input:checked + .toggle-track {
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        box-shadow: inset 0 1px 3px rgba(17, 88, 118, 0.22);
    }

    .masseuse-page .toggle input:checked + .toggle-track .toggle-thumb {
        transform: translateX(1.18rem);
    }

    .masseuse-page .queue-load {
        min-width: 110px;
    }

    .masseuse-page .page-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-weight: 700;
    }

    .masseuse-page .staff-edit-btn {
        width: 2.5rem;
        height: 2.5rem;
        padding: 0;
        border-radius: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(31, 115, 224, 0.14);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(240, 248, 255, 0.95));
        color: #1d67bd;
        box-shadow: 0 8px 18px rgba(31, 115, 224, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .masseuse-page .staff-edit-btn i {
        font-size: 0.95rem;
    }

    .masseuse-page .staff-edit-btn:hover,
    .masseuse-page .staff-edit-btn:focus {
        border-color: transparent;
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        color: #ffffff;
        box-shadow: 0 12px 22px rgba(31, 115, 224, 0.22);
        transform: translateY(-1px);
    }

    .masseuse-page .form-card {
        height: 100%;
    }

    .masseuse-page .upload-note {
        margin-top: 0.45rem;
        font-size: 0.76rem;
        color: #5f7488;
        line-height: 1.35;
    }

    .masseuse-page .upload-note.is-error {
        color: #c54b57;
    }

    .masseuse-page .upload-picker {
        border: 1px dashed rgba(31, 115, 224, 0.24);
        border-radius: 1.15rem;
        background: linear-gradient(180deg, rgba(246, 251, 255, 0.98), rgba(238, 247, 255, 0.94));
        padding: 0.8rem;
    }

    .masseuse-page .upload-stage {
        width: 100%;
        margin: 0;
        cursor: pointer;
    }

    .masseuse-page .upload-frame {
        min-height: 190px;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(31, 115, 224, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .masseuse-page .upload-stage:hover .upload-frame {
        border-color: rgba(20, 184, 154, 0.34);
        box-shadow: 0 10px 24px rgba(17, 81, 146, 0.08);
        transform: translateY(-1px);
    }

    .masseuse-page .upload-picker.has-image .upload-placeholder {
        display: none;
    }

    .masseuse-page .upload-picker:not(.has-image) .upload-preview-image {
        display: none;
    }

    .masseuse-page .upload-preview-image {
        width: 100%;
        max-width: 180px;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 1rem;
        background: #ffffff;
    }

    .masseuse-page .upload-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        text-align: center;
        padding: 1rem;
        color: #54708b;
    }

    .masseuse-page .upload-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(45, 143, 240, 0.14), rgba(20, 184, 154, 0.16));
        color: #1f73e0;
        font-size: 1.7rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }

    .masseuse-page .upload-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: #234262;
    }

    .masseuse-page .upload-subtitle {
        font-size: 0.78rem;
        line-height: 1.35;
        color: #667f96;
        max-width: 16rem;
    }

    .masseuse-page .delete-card {
        margin-top: 1rem;
        border-top: 1px dashed rgba(197, 75, 87, 0.26);
        padding-top: 1rem;
    }

    @media (max-width: 991.98px) {
        .masseuse-page .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .masseuse-page .hero-title {
            font-size: 1.4rem;
        }

        .masseuse-page .stats-grid {
            grid-template-columns: 1fr;
        }

        .masseuse-page .staff-head {
            align-items: flex-start;
        }

        .masseuse-page .staff-toolbar {
            flex-direction: column;
        }

        .masseuse-page .staff-actions {
            width: 100%;
            justify-content: space-between;
            margin-left: 0;
        }

        .masseuse-page .staff-period-switches {
            flex: 1 1 auto;
        }

        .masseuse-page .staff-avatar {
            width: 50px;
            height: 50px;
        }

        .masseuse-page .section-header {
            gap: 0.5rem;
        }

        .masseuse-page .section-action-btn {
            padding: 0.36rem 0.46rem 0.36rem 0.4rem;
            font-size: 0.78rem;
        }

        .masseuse-page .section-action-icon {
            width: 1.72rem;
            height: 1.72rem;
        }

        .masseuse-page .page-action {
            gap: 0.35rem;
        }

        .masseuse-page .summary-panel {
            padding: 0.82rem;
        }

        .masseuse-page .summary-metrics {
            grid-template-columns: 1fr;
            gap: 0.45rem;
        }

        .masseuse-page .summary-metrics.is-two-columns {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 0.45rem;
        }
    }

    .masseuse-page .day-toggle-group {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem;
        border-radius: 0.75rem;
        background: rgba(31, 115, 224, 0.06);
        border: 1px solid rgba(31, 115, 224, 0.1);
    }

    .masseuse-page .day-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 1.8rem;
        padding: 0.2rem 0.7rem;
        border-radius: 0.6rem;
        border: none;
        background: transparent;
        color: #5f7e9c;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.22s ease;
    }

    .masseuse-page .day-toggle-btn:hover {
        color: #1d67bd;
        background: rgba(255, 255, 255, 0.6);
    }

    .masseuse-page .day-toggle-btn.is-active {
        color: #ffffff;
        background: linear-gradient(135deg, #2d8ff0, #14b89a);
        box-shadow: 0 6px 14px rgba(31, 115, 224, 0.22);
    }
</style>
