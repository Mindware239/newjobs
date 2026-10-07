# Blog posts as files

Each `.html` file here becomes a published post on jobsence.com/blog within an hour after it reaches the live server
(the hourly `india_jobs_fetch` cron runs `App\Services\BlogFileImporter`). Name files `YYYY-MM-DD-slug.html`.

```
---
title: How to apply for SSC CGL 2026
slug: how-to-apply-ssc-cgl-2026
excerpt: One or two sentences shown in the blog list.
meta_title: SSC CGL 2026 – How to Apply | Jobsence
meta_description: Up to 160 characters for Google.
meta_keywords: ssc cgl 2026, govt jobs, how to apply
publish_date: 2026-10-08
---
<p>Body HTML – only p, h2–h4, ul/ol/li, strong/em, a, blockquote, table, br, hr are kept.</p>
```

A post is imported once (by slug) and never overwritten – later edits are made in Admin → Blog.
