<?php
/** @var array $featured */
/** @var array $others */
?>
<div x-data="featuredCompanies()">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Featured Companies</h1>
        <p class="mt-2 text-sm text-gray-600">Drag to reorder featured companies. Add from the right list.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Current Featured</h2>
                <form method="POST" action="/admin/companies/featured/order" @submit="prepareSubmit($event)">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <template x-for="(cid, idx) in featuredIds" :key="cid">
                        <input type="hidden" :name="'featured_ids['+idx+']'" :value="cid">
                    </template>
                    <template x-for="(ord, idx) in orderMap" :key="'ord'+idx">
                        <input type="hidden" :name="'order['+idx+']'" :value="ord">
                    </template>
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">Save Order</button>
                </form>
            </div>
            <ul class="space-y-2" x-ref="featuredList">
                <?php foreach ($featured as $f): ?>
                <li class="border rounded-lg p-3 bg-white flex items-center gap-3 cursor-move"
                    draggable="true"
                    @dragstart="onDragStart($event, <?= (int)$f['id'] ?>)"
                    @dragover.prevent
                    @drop="onDrop($event, <?= (int)$f['id'] ?>)">
                    <?php if (!empty($f['logo_url'])): ?>
                        <img src="<?= htmlspecialchars($f['logo_url']) ?>" alt="<?= htmlspecialchars($f['name'] ?? '') ?>" class="w-10 h-10 rounded object-cover">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-600 font-semibold">
                            <?= strtoupper(substr($f['name'] ?? 'C', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <div class="font-semibold"><?= htmlspecialchars($f['name'] ?? '') ?></div>
                        <div class="text-xs text-gray-500">Order: <?= (int)($f['featured_order'] ?? 0) ?></div>
                    </div>
                    <a href="<?= !empty($f['slug']) ? '/company/' . htmlspecialchars($f['slug']) : '/candidate/jobs?company=' . urlencode($f['name'] ?? '') ?>" class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Add Companies</h2>
            <div class="space-y-2">
                <?php foreach ($others as $o): ?>
                <div class="border rounded-lg p-3 bg-white flex items-center gap-3">
                    <?php if (!empty($o['logo_url'])): ?>
                        <img src="<?= htmlspecialchars($o['logo_url']) ?>" alt="<?= htmlspecialchars($o['name'] ?? '') ?>" class="w-10 h-10 rounded object-cover">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-600 font-semibold">
                            <?= strtoupper(substr($o['name'] ?? 'C', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <div class="font-semibold"><?= htmlspecialchars($o['name'] ?? '') ?></div>
                    </div>
                    <button @click="addFeatured(<?= (int)$o['id'] ?>)" class="px-3 py-1.5 border rounded hover:bg-gray-50">Add</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function featuredCompanies() {
    return {
        featuredIds: <?= json_encode(array_values(array_map(fn($f) => (int)$f['id'], $featured))) ?>,
        orderMap: <?= json_encode(array_values(array_map(fn($f, $i) => (int)($f['featured_order'] ?? ($i+1)), $featured, array_keys($featured)))) ?>,
        dragId: null,
        onDragStart(e, id) {
            this.dragId = id;
        },
        onDrop(e, targetId) {
            const fromIdx = this.featuredIds.indexOf(this.dragId);
            const toIdx = this.featuredIds.indexOf(targetId);
            if (fromIdx < 0 || toIdx < 0) return;
            const moved = this.featuredIds.splice(fromIdx, 1)[0];
            this.featuredIds.splice(toIdx, 0, moved);
            this.recomputeOrder();
            this.dragId = null;
        },
        recomputeOrder() {
            this.orderMap = this.featuredIds.map((_, idx) => idx + 1);
        },
        addFeatured(id) {
            if (!this.featuredIds.includes(id)) {
                this.featuredIds.push(id);
                this.recomputeOrder();
            }
        },
        prepareSubmit(e) {
            // nothing extra
        }
    }
}
</script>
