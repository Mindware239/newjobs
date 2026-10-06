<style>
    .sj-hero { background: linear-gradient(135deg, #fff7ed, #fefce8 55%, #ecfdf5); border-bottom: 1px solid #fde68a; }
    .sj-hero h1 { font-size: clamp(1.5rem, 3.2vw, 2.2rem); font-weight: 900; margin: 0 0 6px; }
    .sj-post-cta { display: inline-flex; align-items: center; gap: 8px; background: #059669; color: #fff !important; font-weight: 900; padding: 12px 18px; border-radius: 12px; text-decoration: none; font-size: 1.05rem; }
    .sj-post-cta:hover { background: #047857; }
    .sj-az { display: flex; flex-wrap: wrap; gap: 6px; margin: 14px 0 4px; }
    .sj-az a, .sj-az span { min-width: 34px; text-align: center; padding: 6px 8px; border-radius: 8px; font-weight: 900; text-decoration: none; border: 1px solid #e5e7eb; background: #fff; color: #111827; }
    .sj-az span { color: #d1d5db; }
    .sj-az a:hover { background: #f05537; color: #fff; border-color: #f05537; }
    .sj-letter { margin: 26px 0 8px; font-size: 1.6rem; font-weight: 900; color: #f05537; border-bottom: 3px solid #fde68a; }
    .sj-states { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; }
    .sj-state { display: block; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px; text-decoration: none; color: #111827; }
    .sj-state:hover { border-color: #f05537; box-shadow: 0 2px 8px rgba(240,85,55,.15); }
    .sj-state b { font-size: 1.05rem; }
    .sj-state .n { float: right; background: #fef3c7; color: #92400e; font-weight: 900; border-radius: 999px; padding: 1px 10px; font-size: .85rem; }
    .sj-state small { display: block; color: #6b7280; margin-top: 4px; font-size: .8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sj-list { display: grid; gap: 8px; }
    .sj-job { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px; }
    .sj-job a.t { font-weight: 800; color: #111827; text-decoration: none; }
    .sj-job a.t:hover { color: #f05537; text-decoration: underline; }
    .sj-job .m { grid-column: 1 / -1; font-size: .85rem; color: #4b5563; }
    .sj-job time { font-size: .8rem; color: #374151; font-weight: 700; white-space: nowrap; }
    .sj-badge { display: inline-block; font-size: .7rem; font-weight: 900; padding: 1px 8px; border-radius: 999px; margin-right: 6px; vertical-align: 1px; }
    .sj-badge.free { background: #dcfce7; color: #166534; }
    .sj-badge.jobsence { background: #ffedd5; color: #9a3412; }
    .sj-badge.govt { background: #dbeafe; color: #1e40af; }
    .sj-city { margin: 20px 0 8px; font-size: 1.15rem; font-weight: 900; }
    .sj-city span { color: #6b7280; font-weight: 700; font-size: .9rem; }
    .sj-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 16px; }
    .sj-form .full { grid-column: 1 / -1; }
    .sj-form label { display: flex; flex-direction: column; gap: 4px; font-weight: 700; font-size: .9rem; }
    .sj-form input, .sj-form select, .sj-form textarea { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 10px; font: inherit; font-weight: 400; }
    .sj-form .err { color: #b91c1c; font-size: .82rem; font-weight: 700; }
    @media (max-width: 640px) { .sj-form { grid-template-columns: 1fr; } .sj-job { grid-template-columns: 1fr; } }
</style>
