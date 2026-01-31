<?php
$headerSection = $sectionsData['header'] ?? null;
$headerContent = $headerSection['section_data']['content'] ?? [
    'full_name' => $candidate->attributes['full_name'] ?? '',
    'email' => $candidate->attributes['email'] ?? '',
    'phone' => $candidate->attributes['mobile'] ?? '',
    'location' => ($candidate->attributes['city'] ?? '') . ', ' . ($candidate->attributes['state'] ?? ''),
    'linkedin' => $candidate->attributes['linkedin_url'] ?? '',
    'website' => $candidate->attributes['website_url'] ?? ''
];

// Parse full name into first and last
$fullName = $headerContent['full_name'] ?? '';
$nameParts = explode(' ', $fullName, 2);
$firstName = $nameParts[0] ?? '';
$lastName = $nameParts[1] ?? '';

// Parse location
$locationParts = explode(',', $headerContent['location'] ?? '');
$city = trim($locationParts[0] ?? '');
$country = trim($locationParts[1] ?? '') ?: 'India';
?>
<div class="space-y-6">
    <p class="text-gray-600 mb-8" style="font-size: 16px; line-height: 1.6;">
        Include your full name and multiple ways for employers to reach you.
    </p>

    <div class="grid grid-cols-2 gap-6">
        <div>
            <label class="form-label block">First Name</label>
            <input 
                type="text" 
                x-model="getSection('header').section_data.content.first_name"
                @input="const fn = getSection('header').section_data.content.first_name || ''; const ln = getSection('header').section_data.content.last_name || ''; getSection('header').section_data.content.full_name = (fn + ' ' + ln).trim();"
                @blur="autoSave()"
                value="<?= htmlspecialchars($firstName) ?>"
                class="form-input w-full rounded-lg"
                placeholder="Diya"
                style="font-size: 14px;">
        </div>

        <div>
            <label class="form-label block">Surname</label>
            <input 
                type="text" 
                x-model="getSection('header').section_data.content.last_name"
                @input="const fn = getSection('header').section_data.content.first_name || ''; const ln = getSection('header').section_data.content.last_name || ''; getSection('header').section_data.content.full_name = (fn + ' ' + ln).trim();"
                @blur="autoSave()"
                value="<?= htmlspecialchars($lastName) ?>"
                class="form-input w-full rounded-lg"
                placeholder="Agarwal"
                style="font-size: 14px;">
        </div>

        <div>
            <label class="form-label block">City</label>
            <input 
                type="text" 
                x-model="getSection('header').section_data.content.city"
                @input="const city = getSection('header').section_data.content.city || ''; const country = getSection('header').section_data.content.country || ''; getSection('header').section_data.content.location = [city, country].filter(Boolean).join(', ');"
                @blur="autoSave()"
                value="<?= htmlspecialchars($city) ?>"
                class="form-input w-full rounded-lg"
                placeholder="New Delhi"
                style="font-size: 14px;">
        </div>

        <div>
            <label class="form-label block">Country</label>
            <input 
                type="text" 
                x-model="getSection('header').section_data.content.country"
                @input="const city = getSection('header').section_data.content.city || ''; const country = getSection('header').section_data.content.country || ''; getSection('header').section_data.content.location = [city, country].filter(Boolean).join(', ');"
                @blur="autoSave()"
                value="<?= htmlspecialchars($country) ?>"
                class="form-input w-full rounded-lg"
                placeholder="India"
                style="font-size: 14px;">
        </div>

        <div>
            <label class="form-label block">Pin Code</label>
            <input 
                type="text" 
                x-model="getSection('header').section_data.content.pin_code"
                @blur="autoSave()"
                value=""
                class="form-input w-full rounded-lg"
                placeholder="110034"
                style="font-size: 14px;">
        </div>

        <div>
            <label class="form-label block">Phone</label>
            <input 
                type="tel" 
                x-model="getSection('header').section_data.content.phone"
                @blur="autoSave()"
                value="<?= htmlspecialchars($headerContent['phone'] ?? '') ?>"
                class="form-input w-full rounded-lg"
                placeholder="+91 11 1234 5677"
                style="font-size: 14px;">
        </div>

        <div class="col-span-2">
            <label class="form-label block">Email <span class="text-red-500">*</span></label>
            <input 
                type="email" 
                x-model="getSection('header').section_data.content.email"
                @blur="autoSave()"
                value="<?= htmlspecialchars($headerContent['email'] ?? '') ?>"
                class="form-input w-full rounded-lg"
                placeholder="d.agarwal@sample.in"
                required
                style="font-size: 14px;">
        </div>
    </div>
</div>
