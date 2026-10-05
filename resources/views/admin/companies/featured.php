<?php
/** @var array $featured */
/** @var array $others */

// Merge all companies for table view
$all_companies = array_merge(
    array_map(fn($c) => array_merge($c, ['is_featured' => true]), array_values($featured)),
    array_map(fn($c) => array_merge($c, ['is_featured' => false]), array_values($others))
);
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; }
.fcp-wrap { font-family: 'Plus Jakarta Sans', sans-serif; padding: 28px 32px; max-width: 1500px; margin: 0 auto; }

:root {
    --orange: #F97316;
    --orange-dark: #EA580C;
    --orange-light: #FFF7ED;
    --orange-border: #FED7AA;
    --green: #10B981;
    --green-light: #ECFDF5;
    --red: #EF4444;
    --red-light: #FEF2F2;
    --gray-50: #F9FAFB;
    --gray-100: #F3F4F6;
    --gray-200: #E5E7EB;
    --gray-300: #D1D5DB;
    --gray-400: #9CA3AF;
    --gray-500: #6B7280;
    --gray-700: #374151;
    --gray-900: #111827;
    --white: #FFFFFF;
    --radius: 14px;
    --radius-sm: 8px;
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 8px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.07);
}

/* ── Page Header ── */
.fcp-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; margin-bottom: 24px;
}
.fcp-title { font-size: 22px; font-weight: 800; color: var(--gray-900); margin: 0 0 4px; letter-spacing: -0.03em; }
.fcp-sub { font-size: 13px; color: var(--gray-400); font-weight: 500; margin: 0; }
.fcp-header-actions { display: flex; align-items: center; gap: 10px; }

.fcp-stat {
    display: flex; align-items: center; gap: 8px;
    background: var(--orange-light); border: 1px solid var(--orange-border);
    border-radius: var(--radius-sm); padding: 8px 14px;
    font-size: 13px; font-weight: 700; color: var(--orange-dark);
}
.fcp-stat-num { font-size: 20px; font-weight: 800; color: var(--orange); }

.fcp-btn-primary {
    display: flex; align-items: center; gap: 7px;
    padding: 10px 20px;
    background: var(--orange); color: #fff;
    border: none; border-radius: var(--radius-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 700; cursor: pointer;
    box-shadow: 0 3px 12px rgba(249,115,22,0.35);
    transition: all 0.15s;
}
.fcp-btn-primary:hover { background: var(--orange-dark); transform: translateY(-1px); }
.fcp-btn-primary svg { width: 15px; height: 15px; }

/* ── Toolbar ── */
.fcp-toolbar {
    display: flex; align-items: center; gap: 10px;
    background: var(--white); border: 1px solid var(--gray-200);
    border-radius: var(--radius); padding: 14px 18px;
    margin-bottom: 12px; box-shadow: var(--shadow); flex-wrap: wrap;
}
.fcp-search-wrap { position: relative; flex: 1; min-width: 200px; }
.fcp-search {
    width: 100%; padding: 9px 14px 9px 36px;
    background: var(--gray-50); border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-sm); font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 500; color: var(--gray-900);
    outline: none; transition: all 0.15s;
}
.fcp-search:focus { background: var(--white); border-color: var(--orange); box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
.fcp-search::placeholder { color: var(--gray-400); }
.fcp-search-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--gray-400); }
.fcp-search-icon svg { width: 15px; height: 15px; display: block; }

.fcp-select {
    padding: 9px 32px 9px 12px; background: var(--gray-50) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239CA3AF' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 10px center;
    border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
    font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 500;
    color: var(--gray-700); cursor: pointer; outline: none; appearance: none;
    transition: border-color 0.15s;
}
.fcp-select:focus { border-color: var(--orange); }

.fcp-date-input {
    padding: 9px 12px; background: var(--gray-50);
    border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm);
    font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 500;
    color: var(--gray-700); outline: none; cursor: pointer; transition: border-color 0.15s;
}
.fcp-date-input:focus { border-color: var(--orange); }

.fcp-toolbar-divider { width: 1px; height: 28px; background: var(--gray-200); flex-shrink: 0; }

.fcp-filter-label { font-size: 12px; font-weight: 700; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.06em; white-space: nowrap; }

/* ── Bulk Actions Bar ── */
.fcp-bulk-bar {
    display: none; align-items: center; gap: 12px;
    background: var(--orange-light); border: 1px solid var(--orange-border);
    border-radius: var(--radius); padding: 10px 18px; margin-bottom: 12px;
    font-size: 13px; font-weight: 600; color: var(--orange-dark);
}
.fcp-bulk-bar.active { display: flex; }
.fcp-bulk-count { font-weight: 800; }
.fcp-bulk-actions { display: flex; gap: 8px; margin-left: auto; }
.fcp-bulk-btn {
    padding: 6px 14px; border-radius: 6px; font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12px; font-weight: 700; cursor: pointer; border: none; transition: all 0.15s;
}
.fcp-bulk-btn.feature { background: var(--orange); color: #fff; }
.fcp-bulk-btn.feature:hover { background: var(--orange-dark); }
.fcp-bulk-btn.unfeature { background: var(--white); border: 1.5px solid var(--gray-300); color: var(--gray-700); }
.fcp-bulk-btn.unfeature:hover { border-color: var(--red); color: var(--red); }
.fcp-bulk-btn.clear { background: transparent; color: var(--gray-500); }
.fcp-bulk-btn.clear:hover { color: var(--gray-900); }

/* ── Table ── */
.fcp-table-wrap {
    background: var(--white); border: 1px solid var(--gray-200);
    border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
}
table.fcp-table { width: 100%; border-collapse: collapse; }
.fcp-table thead tr {
    background: var(--gray-50); border-bottom: 1.5px solid var(--gray-200);
}
.fcp-table th {
    padding: 12px 16px; text-align: left;
    font-size: 11px; font-weight: 800; color: var(--gray-400);
    text-transform: uppercase; letter-spacing: 0.08em; white-space: nowrap;
    user-select: none;
}
.fcp-table th.sortable { cursor: pointer; }
.fcp-table th.sortable:hover { color: var(--gray-700); }
.fcp-table th .sort-icon { display: inline-block; margin-left: 4px; opacity: 0.4; font-size: 10px; }
.fcp-table th.sorted .sort-icon { opacity: 1; color: var(--orange); }

.fcp-table tbody tr {
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.12s;
}
.fcp-table tbody tr:last-child { border-bottom: none; }
.fcp-table tbody tr:hover { background: var(--gray-50); }
.fcp-table tbody tr.selected { background: var(--orange-light) !important; }

.fcp-table td { padding: 13px 16px; font-size: 13px; color: var(--gray-700); vertical-align: middle; }

/* Checkbox */
.fcp-cb {
    width: 17px; height: 17px; cursor: pointer; accent-color: var(--orange);
    border-radius: 4px;
}

/* Rank cell */
.fcp-rank-cell { display: flex; align-items: center; gap: 8px; }
.fcp-rank-badge {
    width: 24px; height: 24px; border-radius: 50%;
    background: var(--orange); color: #fff;
    font-size: 11px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 2px 6px rgba(249,115,22,0.4);
}
.fcp-rank-dash { width: 24px; height: 24px; border-radius: 50%; background: var(--gray-100); display: flex; align-items: center; justify-content: center; }
.fcp-rank-dash svg { width: 12px; height: 12px; color: var(--gray-400); }

/* Company cell */
.fcp-company-cell { display: flex; align-items: center; gap: 11px; }
.fcp-avatar {
    width: 40px; height: 40px; border-radius: 10px;
    object-fit: contain; background: var(--gray-100); border: 1px solid var(--gray-200); padding: 4px;
    flex-shrink: 0;
}
.fcp-avatar-letter {
    width: 40px; height: 40px; border-radius: 10px;
    background: var(--gray-100); color: var(--gray-500);
    font-size: 16px; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.fcp-company-name { font-weight: 700; color: var(--gray-900); font-size: 13px; }
.fcp-company-id { font-size: 11px; color: var(--gray-400); font-weight: 600; margin-top: 1px; }

/* Status badge */
.fcp-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
}
.fcp-status.featured { background: var(--green-light); color: var(--green); }
.fcp-status.featured::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--green); display: block; }
.fcp-status.available { background: var(--gray-100); color: var(--gray-500); }
.fcp-status.available::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--gray-400); display: block; }

/* Actions */
.fcp-actions { display: flex; align-items: center; gap: 6px; }
.fcp-action-btn {
    display: flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: var(--radius-sm);
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 11px; font-weight: 700; cursor: pointer; border: none;
    transition: all 0.15s; white-space: nowrap;
}
.fcp-action-btn.add { background: var(--orange-light); color: var(--orange-dark); border: 1px solid var(--orange-border); }
.fcp-action-btn.add:hover { background: var(--orange); color: #fff; border-color: var(--orange); }
.fcp-action-btn.remove { background: var(--red-light); color: var(--red); border: 1px solid #FECACA; }
.fcp-action-btn.remove:hover { background: var(--red); color: #fff; }
.fcp-action-btn svg { width: 13px; height: 13px; }

.fcp-profile-link {
    font-size: 11px; font-weight: 700; color: var(--orange);
    text-decoration: none; display: flex; align-items: center; gap: 4px;
}
.fcp-profile-link:hover { text-decoration: underline; }
.fcp-profile-link svg { width: 11px; height: 11px; }

/* Drag handle */
.fcp-drag { color: var(--gray-300); cursor: grab; }
.fcp-drag:hover { color: var(--orange); }
.fcp-drag svg { width: 16px; height: 16px; display: block; }

/* Empty */
.fcp-empty-row td { padding: 48px; text-align: center; color: var(--gray-400); font-size: 13px; font-weight: 600; }

/* Footer */
.fcp-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; background: var(--gray-50); border-top: 1px solid var(--gray-200);
    font-size: 12px; color: var(--gray-500); font-weight: 500;
}

/* Drag states */
tr.drag-over td { background: var(--orange-light) !important; }
tr.dragging { opacity: 0.4; }
</style>

<div x-data="featuredCompanies()" class="fcp-wrap">

    <!-- ── Header ── -->
    <div class="fcp-header">
        <div>
            <h1 class="fcp-title">Featured Companies</h1>
            <p class="fcp-sub">Manage which companies appear in high-visibility spots on the homepage</p>
        </div>
        <div class="fcp-header-actions">
            <div class="fcp-stat">
                <span class="fcp-stat-num" x-text="featured.length"></span>
                <span>Active Spots</span>
            </div>
            <form method="POST" action="/admin/companies/featured/order" @submit="prepareSubmit($event)">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <template x-for="(cid, idx) in featuredIds" :key="cid">
                    <input type="hidden" :name="'featured_ids['+idx+']'" :value="cid">
                </template>
                <template x-for="(ord, idx) in orderMap" :key="'ord'+idx">
                    <input type="hidden" :name="'order['+idx+']'" :value="ord">
                </template>
                <button type="submit" class="fcp-btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Save Placements
                </button>
            </form>
        </div>
    </div>

    <!-- ── Toolbar ── -->
    <div class="fcp-toolbar">
        <!-- Search -->
        <div class="fcp-search-wrap">
            <div class="fcp-search-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
            <input type="text" x-model="search" placeholder="Search company name or ID..." class="fcp-search">
        </div>

        <div class="fcp-toolbar-divider"></div>
        <span class="fcp-filter-label">Filter</span>

        <!-- Status filter -->
        <select x-model="filterStatus" class="fcp-select">
            <option value="all">All Companies</option>
            <option value="featured">Featured Only</option>
            <option value="available">Available Only</option>
        </select>

        <div class="fcp-toolbar-divider"></div>
        <span class="fcp-filter-label">Date Added</span>

        <!-- Date filters -->
        <input type="date" x-model="dateFrom" class="fcp-date-input" title="From date">
        <span style="font-size:12px;color:var(--gray-400);font-weight:600;">to</span>
        <input type="date" x-model="dateTo" class="fcp-date-input" title="To date">

        <!-- Clear -->
        <button @click="clearFilters()" style="padding:9px 14px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);background:var(--gray-50);font-family:'Plus Jakarta Sans',sans-serif;font-size:12px;font-weight:700;color:var(--gray-500);cursor:pointer;" title="Clear filters">
            ✕ Clear
        </button>
    </div>

    <!-- ── Bulk Bar ── -->
    <div class="fcp-bulk-bar" :class="{ active: selected.length > 0 }">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><span class="fcp-bulk-count" x-text="selected.length"></span> companies selected</span>
        <div class="fcp-bulk-actions">
            <button @click="bulkFeature()" class="fcp-bulk-btn feature">★ Mark as Featured</button>
            <button @click="bulkUnfeature()" class="fcp-bulk-btn unfeature">✕ Remove from Featured</button>
            <button @click="selected = []" class="fcp-bulk-btn clear">Deselect All</button>
        </div>
    </div>

    <!-- ── Table ── -->
    <div class="fcp-table-wrap">
        <table class="fcp-table">
            <thead>
                <tr>
                    <th style="width:44px;">
                        <input type="checkbox" class="fcp-cb"
                               :checked="selected.length === filteredAll.length && filteredAll.length > 0"
                               @change="toggleAll($event)">
                    </th>
                    <th style="width:60px;" class="sortable" @click="setSort('rank')" :class="{sorted: sortBy==='rank'}">
                        Rank <span class="sort-icon" x-text="sortBy==='rank' ? (sortDir==='asc'?'▲':'▼') : '⇅'"></span>
                    </th>
                    <th class="sortable" @click="setSort('name')" :class="{sorted: sortBy==='name'}">
                        Company <span class="sort-icon" x-text="sortBy==='name' ? (sortDir==='asc'?'▲':'▼') : '⇅'"></span>
                    </th>
                    <th style="width:120px;">Status</th>
                    <th style="width:130px;">Public Profile</th>
                    <th style="width:130px;" class="sortable" @click="setSort('date')" :class="{sorted: sortBy==='date'}">
                        Date Added <span class="sort-icon" x-text="sortBy==='date' ? (sortDir==='asc'?'▲':'▼') : '⇅'"></span>
                    </th>
                    <th style="width:56px;">Order</th>
                    <th style="width:180px; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody x-ref="tableBody">

                <template x-for="company in sortedFiltered" :key="company.id">
                    <tr :class="{ selected: selected.includes(company.id), dragging: dragId === company.id }"
                        x-show="company.is_featured"
                        draggable="true"
                        @dragstart="onDragStart($event, company.id)"
                        @dragend="onDragEnd($event)"
                        @dragover.prevent="onDragOver($event)"
                        @dragleave="onDragLeave($event)"
                        @drop="onDrop($event, company.id)">

                        <!-- Checkbox -->
                        <td>
                            <input type="checkbox" class="fcp-cb"
                                   :value="company.id"
                                   :checked="selected.includes(company.id)"
                                   @change="toggleSelect(company.id)">
                        </td>

                        <!-- Rank -->
                        <td>
                            <div class="fcp-rank-cell">
                                <template x-if="company.is_featured">
                                    <div class="fcp-rank-badge" x-text="featuredIds.indexOf(company.id) + 1"></div>
                                </template>
                                <template x-if="!company.is_featured">
                                    <div class="fcp-rank-dash"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;color:var(--gray-400)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></div>
                                </template>
                            </div>
                        </td>

                        <!-- Company -->
                        <td>
                            <div class="fcp-company-cell">
                                <template x-if="company.logo_url">
                                    <img :src="company.logo_url" class="fcp-avatar">
                                </template>
                                <template x-if="!company.logo_url">
                                    <div class="fcp-avatar-letter" x-text="company.name.charAt(0)"></div>
                                </template>
                                <div>
                                    <div class="fcp-company-name" x-text="company.name"></div>
                                    <div class="fcp-company-id" x-text="'ID: #' + company.id"></div>
                                </div>
                            </div>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="fcp-status" :class="company.is_featured ? 'featured' : 'available'"
                                  x-text="company.is_featured ? 'Featured' : 'Available'"></span>
                        </td>

                        <!-- Profile link -->
                        <td>
                            <a :href="'/company/' + company.slug" target="_blank" class="fcp-profile-link">
                                View Profile
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </td>

                        <!-- Date -->
                        <td style="font-size:12px; color:var(--gray-400); font-weight:600;" x-text="company.created_at ? formatDate(company.created_at) : '—'"></td>

                        <!-- Drag handle -->
                        <td>
                            <template x-if="company.is_featured">
                                <div class="fcp-drag" title="Drag to reorder">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <circle cx="7" cy="5" r="1.4"/><circle cx="13" cy="5" r="1.4"/>
                                        <circle cx="7" cy="10" r="1.4"/><circle cx="13" cy="10" r="1.4"/>
                                        <circle cx="7" cy="15" r="1.4"/><circle cx="13" cy="15" r="1.4"/>
                                    </svg>
                                </div>
                            </template>
                        </td>

                        <!-- Actions -->
                        <td style="text-align:right;">
                            <div class="fcp-actions" style="justify-content:flex-end;">
                                <template x-if="!company.is_featured">
                                    <button @click="addFeatured(company.id)" class="fcp-action-btn add">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Add to Featured
                                    </button>
                                </template>
                                <template x-if="company.is_featured">
                                    <button @click="removeFeatured(company.id)" class="fcp-action-btn remove">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Remove
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>

                <!-- Available rows (non-featured) shown below -->
                <template x-for="company in sortedFiltered" :key="'av-'+company.id">
                    <tr :class="{ selected: selected.includes(company.id) }"
                        x-show="!company.is_featured">

                        <td>
                            <input type="checkbox" class="fcp-cb"
                                   :value="company.id"
                                   :checked="selected.includes(company.id)"
                                   @change="toggleSelect(company.id)">
                        </td>
                        <td>
                            <div class="fcp-rank-dash" style="margin:0;"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;color:var(--gray-400)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></div>
                        </td>
                        <td>
                            <div class="fcp-company-cell">
                                <template x-if="company.logo_url">
                                    <img :src="company.logo_url" class="fcp-avatar">
                                </template>
                                <template x-if="!company.logo_url">
                                    <div class="fcp-avatar-letter" x-text="company.name.charAt(0)"></div>
                                </template>
                                <div>
                                    <div class="fcp-company-name" x-text="company.name"></div>
                                    <div class="fcp-company-id" x-text="'ID: #' + company.id"></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="fcp-status available">Available</span></td>
                        <td>
                            <a :href="'/company/' + company.slug" target="_blank" class="fcp-profile-link">
                                View Profile
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </td>
                        <td style="font-size:12px; color:var(--gray-400); font-weight:600;" x-text="company.created_at ? formatDate(company.created_at) : '—'"></td>
                        <td></td>
                        <td style="text-align:right;">
                            <div class="fcp-actions" style="justify-content:flex-end;">
                                <button @click="addFeatured(company.id)" class="fcp-action-btn add">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Add to Featured
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>

                <!-- Empty state -->
                <tr class="fcp-empty-row" x-show="sortedFiltered.length === 0">
                    <td colspan="8">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:32px;height:32px;color:var(--gray-300);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            No companies match your filters
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="fcp-footer">
            <span x-text="'Showing ' + sortedFiltered.length + ' of ' + allCompanies.length + ' companies'"></span>
            <span x-text="featured.length + ' featured · ' + (allCompanies.length - featured.length) + ' available'"></span>
        </div>
    </div>
</div>

<script>
function featuredCompanies() {
    return {
        featured: <?= json_encode(array_values($featured)) ?>,
        others: <?= json_encode(array_values($others)) ?>,
        search: '',
        filterStatus: 'all',
        dateFrom: '',
        dateTo: '',
        selected: [],
        sortBy: 'rank',
        sortDir: 'asc',
        dragId: null,

        get allCompanies() {
            const featuredIds = this.featured.map(f => parseInt(f.id));
            const featuredList = this.featured.map(f => ({ ...f, is_featured: true }));
            const otherList = this.others
                .filter(o => !featuredIds.includes(parseInt(o.id)))
                .map(o => ({ ...o, is_featured: false }));
            return [...featuredList, ...otherList];
        },

        get featuredIds() {
            return this.featured.map(f => parseInt(f.id));
        },

        get orderMap() {
            return this.featured.map((_, idx) => idx + 1);
        },

        get filteredAll() {
            const s = this.search.toLowerCase();
            return this.allCompanies.filter(c => {
                const matchSearch = !s || c.name.toLowerCase().includes(s) || String(c.id).includes(s);
                const matchStatus = this.filterStatus === 'all' ||
                    (this.filterStatus === 'featured' && c.is_featured) ||
                    (this.filterStatus === 'available' && !c.is_featured);
                let matchDate = true;
                if (c.created_at) {
                    const d = new Date(c.created_at);
                    if (this.dateFrom) matchDate = matchDate && d >= new Date(this.dateFrom);
                    if (this.dateTo) matchDate = matchDate && d <= new Date(this.dateTo + 'T23:59:59');
                }
                return matchSearch && matchStatus && matchDate;
            });
        },

        get sortedFiltered() {
            const list = [...this.filteredAll];
            list.sort((a, b) => {
                let va, vb;
                if (this.sortBy === 'rank') {
                    va = a.is_featured ? this.featuredIds.indexOf(parseInt(a.id)) : 9999;
                    vb = b.is_featured ? this.featuredIds.indexOf(parseInt(b.id)) : 9999;
                } else if (this.sortBy === 'name') {
                    va = a.name.toLowerCase(); vb = b.name.toLowerCase();
                } else if (this.sortBy === 'date') {
                    va = new Date(a.created_at || 0); vb = new Date(b.created_at || 0);
                }
                if (va < vb) return this.sortDir === 'asc' ? -1 : 1;
                if (va > vb) return this.sortDir === 'asc' ? 1 : -1;
                return 0;
            });
            return list;
        },

        setSort(col) {
            if (this.sortBy === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            else { this.sortBy = col; this.sortDir = 'asc'; }
        },

        clearFilters() {
            this.search = ''; this.filterStatus = 'all'; this.dateFrom = ''; this.dateTo = '';
        },

        toggleAll(e) {
            this.selected = e.target.checked ? this.filteredAll.map(c => c.id) : [];
        },

        toggleSelect(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) this.selected.push(id);
            else this.selected.splice(idx, 1);
        },

        bulkFeature() {
            this.selected.forEach(id => this.addFeatured(id));
            this.selected = [];
        },

        bulkUnfeature() {
            this.selected.forEach(id => this.removeFeatured(id));
            this.selected = [];
        },

        addFeatured(id) {
            const all = [...this.featured, ...this.others];
            const company = all.find(o => parseInt(o.id) === parseInt(id));
            if (company && !this.featuredIds.includes(parseInt(id))) {
                this.featured.push({ ...company });
            }
        },

        removeFeatured(id) {
            const idx = this.featured.findIndex(f => parseInt(f.id) === parseInt(id));
            if (idx !== -1) this.featured.splice(idx, 1);
        },

        formatDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        onDragStart(e, id) {
            this.dragId = id;
            e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => e.target.classList.add('dragging'), 0);
        },
        onDragEnd(e) {
            this.dragId = null;
            document.querySelectorAll('tr').forEach(r => r.classList.remove('dragging', 'drag-over'));
        },
        onDragOver(e) {
            document.querySelectorAll('tr').forEach(r => r.classList.remove('drag-over'));
            e.currentTarget.classList.add('drag-over');
        },
        onDragLeave(e) { e.currentTarget.classList.remove('drag-over'); },
        onDrop(e, targetId) {
            e.currentTarget.classList.remove('drag-over');
            const fromIdx = this.featured.findIndex(f => parseInt(f.id) === parseInt(this.dragId));
            const toIdx = this.featured.findIndex(f => parseInt(f.id) === parseInt(targetId));
            if (fromIdx !== -1 && toIdx !== -1) {
                const moved = this.featured.splice(fromIdx, 1)[0];
                this.featured.splice(toIdx, 0, moved);
            }
            this.dragId = null;
        },

        prepareSubmit(e) {
            if (this.featured.length === 0) {
                if (!confirm('You are about to have ZERO featured companies. Continue?')) e.preventDefault();
            }
        }
    }
}
</script>