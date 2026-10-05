<style>
.sd .ij-hero { background: linear-gradient(135deg, #fff1ed 0%, #fff 70%); border-bottom: 1px solid #e5e7eb; }
.sd .ij-hero h1 { font-size: clamp(1.7rem, 4.5vw, 2.5rem); font-weight: 900; margin: 0 0 6px; line-height: 1.15; }
.sd .ij-sub { color: #4b5563; margin: 0 0 16px; max-width: 760px; }
.sd .ij-live { display: inline-block; vertical-align: middle; margin-right: 10px; width: 12px; height: 12px; border-radius: 50%; background: #dc2626; box-shadow: 0 0 0 0 rgba(220, 38, 38, .6); animation: ijPulse 1.4s infinite; flex: none; }
@keyframes ijPulse { 0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, .6); } 70% { box-shadow: 0 0 0 10px rgba(220, 38, 38, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); } }
.sd .ij-pass { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; background: #fff; border: 2px dashed #f05537; border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; }
.sd .ij-tabs { display: flex; align-items: center; gap: 8px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 12px; scrollbar-width: thin; }
.sd .ij-tabs a { flex: none; padding: 8px 14px; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; color: #1f2937; font-weight: 700; font-size: .9rem; text-decoration: none; white-space: nowrap; }
.sd .ij-tabs a span { color: #6b7280; font-weight: 600; margin-left: 2px; }
.sd .ij-tabs a.on { background: #f05537; border-color: #f05537; color: #fff; }
.sd .ij-tabs a.on span { color: #ffe4dc; }
.sd .ij-kinds a { border-radius: 10px; }
.sd .ij-kinds a.on { background: #111827; border-color: #111827; }
.sd .ij-filter { display: grid; grid-template-columns: 1fr 220px auto; gap: 8px; }
.sd .ij-filter input, .sd .ij-filter select { padding: 11px 12px; border: 1px solid #d1d5db; border-radius: 10px; font: inherit; min-width: 0; background: #fff; }
.sd .ij-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.sd .ij-chips a { padding: 5px 11px; border-radius: 999px; background: #fff; border: 1px dashed #f05537; color: #c2410c; font-size: .82rem; font-weight: 700; text-decoration: none; }
.sd .ij-chips a.on, .sd .ij-chips a:hover { background: #f05537; color: #fff; border-style: solid; }
@media (max-width: 700px) { .sd .ij-filter { grid-template-columns: 1fr; } }
.sd .ij-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
@media (max-width: 400px) { .sd .ij-list { grid-template-columns: 1fr; } }
.sd .ij-job { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 6px; box-shadow: 0 1px 2px rgba(0, 0, 0, .04); }
.sd .ij-job h2 { font-size: 1.05rem; margin: 2px 0 0; line-height: 1.35; }
.sd .ij-job h2 a { color: #111827; text-decoration: none; }
.sd .ij-job h2 a:hover { color: #f05537; }
.sd .ij-job-top { display: flex; justify-content: space-between; gap: 8px; flex-wrap: wrap; align-items: center; }
.sd .ij-org { color: #4b5563; font-size: .9rem; }
.sd .ij-sum { color: #374151; font-size: .9rem; margin: 2px 0; }
.sd .ij-more { margin-top: auto; color: #f05537; font-weight: 800; text-decoration: none; font-size: .92rem; }
.sd .ij-badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .75rem; font-weight: 800; background: #f3f4f6; color: #374151; width: fit-content; }
.sd .ij-badge.t-railways { background: #dbeafe; color: #1e40af; }
.sd .ij-badge.t-defence { background: #dcfce7; color: #166534; }
.sd .ij-badge.t-police { background: #e0e7ff; color: #3730a3; }
.sd .ij-badge.t-state_govt, .sd .ij-badge.t-central_govt { background: #fef3c7; color: #92400e; }
.sd .ij-badge.t-psu { background: #fce7f3; color: #9d174d; }
.sd .ij-badge.t-bank { background: #ccfbf1; color: #115e59; }
.sd .ij-badge.t-private, .sd .ij-badge.t-jobsence { background: #fff1ed; color: #c2410c; }
.sd .ij-date { font-size: .8rem; color: #4b5563; font-weight: 700; }
.sd .ij-date.soon { color: #dc2626; }
.sd .ij-pager { display: flex; justify-content: center; align-items: center; gap: 12px; margin: 20px 0; }
.sd .ij-note { margin-top: 24px; font-size: .85rem; color: #4b5563; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px; }
.sd .ij-details { white-space: normal; color: #1f2937; line-height: 1.6; }
.sd .ij-lock { text-align: center; border: 2px dashed #f05537; border-radius: 14px; padding: 20px 16px; background: #fff7f5; margin-top: 8px; }
.sd .ij-lock-ico { font-size: 2rem; }
.sd .ij-lock p { color: #4b5563; margin: 6px 0 14px; }
@media (prefers-reduced-motion: reduce) { .sd .ij-live { animation: none; } }
</style>
