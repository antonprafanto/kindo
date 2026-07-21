<style>
    .fi-fo-rich-editor-floating-toolbar {
        animation: kindo-float-toolbar-in 0.16s ease-out;
        box-shadow:
            0 10px 28px rgba(15, 23, 42, 0.18),
            0 0 0 1px rgba(41, 121, 255, 0.12);
        border-radius: 0.65rem;
    }

    .fi-fo-rich-editor-floating-toolbar .fi-fo-rich-editor-tool[aria-pressed="true"],
    .fi-fo-rich-editor-floating-toolbar .fi-fo-rich-editor-tool[data-active="true"] {
        background-color: rgb(41 121 255 / 0.14);
        color: #2979ff;
    }

    @keyframes kindo-float-toolbar-in {
        from {
            opacity: 0;
            transform: translateY(6px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @media (max-width: 640px) {
        .fi-fo-rich-editor-floating-toolbar .fi-fo-rich-editor-tool {
            min-height: 2.5rem;
            min-width: 2.5rem;
        }
    }

    /* Light neo-brutal accents — closer to public brand (P1-07) */
    .fi-btn {
        border-radius: 0 !important;
        border-width: 2px !important;
    }
    .fi-sidebar-header,
    .fi-topbar {
        border-bottom: 2px solid #000 !important;
    }
    .fi-wi-stats-overview-stat {
        border: 2px solid #000 !important;
        border-radius: 0 !important;
        box-shadow: 3px 3px 0 #000 !important;
    }
</style>
