<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Employer Registration - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .step-connector::after {
            content: '';
            position: absolute;
            top: 12px;
            left: 20px;
            width: 1px;
            height: calc(100% - 24px);
            background: #e5e7eb;
            z-index: 0;
        }

        .step-item {
            position: relative;
            z-index: 1;
        }

        .file-upload-area {
            border: 2px dashed #e5e7eb;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .file-upload-area:hover,
        .file-upload-area.active {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }

        .file-upload-area.accept {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }
        .document-preview-container {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }
        .document-preview-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .document-preview-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .document-preview-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .document-preview-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .document-preview-container {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }
        .document-preview-iframe {
            width: 100%;
            min-height: 400px;
            border: none;
            background: white;
        }
        .document-preview-image {
            width: 100%;
            height: auto;
            display: block;
        }
    </style>
    <script>
        window.flagUrlFromIso = function (iso, size = '24x18') {
            const cc = String(iso || '').toLowerCase();
            if (!cc || cc.length !== 2) return '';
            return `https://flagcdn.com/${size}/${cc}.png`;
        };
        window.flagFromIso = function (iso) {
            const cc = String(iso || '').toUpperCase();
            if (!cc || cc.length !== 2) return '';
            const A = 127397;
            return String.fromCodePoint(A + cc.charCodeAt(0)) + String.fromCodePoint(A + cc.charCodeAt(1));
        };
        window.phoneMax = function (countryCode) {
            const code = String(countryCode || '');
            return code.startsWith('+91') ? 10 : 15;
        };
        window.registrationForm = function () {
            return {
                currentStep: 1,
                isSubmitting: false,
                isSaving: false,
                countries: (window.countriesData || []).sort((a, b) => a.name.localeCompare(b.name)),
                formData: {
                    email: '',
                    password: '',
                    company_name: '',
                    phone: '',
                    country_code: '+91',
                    country: '',
                    company_type: 'national',
                    website: '',
                    description: '',
                    industry: '',
                    company_size: '',
                    address: {
                        street: '',
                        city: '',
                        state: '',
                        postal_code: '',
                        lat: null,
                        lng: null
                    },
                    tax_id: '',
                    documents: {
                        business_license: null,
                        tax_id: null,
                        address_proof: null,
                        director_id: null,
                        other: null
                    },
                    accept_terms: false
                },
                showPassword: false,
                phoneCodeOpen: false,
                phoneCodeSearch: '',
                passwordValid: false,
                passwordError: '',
                passwordStrengthText: '',
                passwordStrengthTextClass: '',
                passwordStrengthBarClass: 'bg-red-500',
                passwordStrengthBarStyle: 'width: 0%',
                passwordSuggestions: [],
                map: null,
                marker: null,
                geocodeTimer: null,
                init() {
                    const hydrateCountries = () => {
                        if (Array.isArray(window.countriesData) && window.countriesData.length) {
                            this.countries = window.countriesData
                                .map(c => ({ ...c, flag: this.flagFromIso(c.code || '') }))
                                .sort((a, b) => a.name.localeCompare(b.name));
                            return true;
                        }
                        return false;
                    };
                    if (!hydrateCountries()) {
                        const t = setInterval(() => {
                            if (hydrateCountries()) clearInterval(t);
                        }, 50);
                        // Load saved data if available
                        this.loadSavedData();
                        // Fallback: fetch countries + dial codes + ISO codes if not preloaded
                        fetch('https://restcountries.com/v3.1/all?fields=name,idd,cca2')
                            .then(r => r.json())
                            .then(list => {
                                const mapped = (list || [])
                                    .map(c => {
                                        const root = (c.idd && c.idd.root) ? c.idd.root : '';
                                        const suffix = (c.idd && Array.isArray(c.idd.suffixes) && c.idd.suffixes.length) ? c.idd.suffixes[0] : '';
                                        const phone = (root + suffix) || '';
                                        return {
                                            name: c.name?.common || '',
                                            code: (c.cca2 || '').toUpperCase(),
                                            phone: phone || '',
                                            flag: this.flagFromIso(c.cca2 || '')
                                        };
                                    })
                                    .filter(c => c.name);
                                if (mapped.length) {
                                    window.countriesData = mapped;
                                    this.countries = mapped.sort((a, b) => a.name.localeCompare(b.name));
                                }
                            })
                            .catch(() => {});
                    }
                    this.loadSavedData();
                    this.$watch('formData.password', () => this.evaluatePassword());
                    this.$watch('currentStep', (step) => {
                        if (step === 2) {
                            this.$nextTick(() => {
                                this.initMap();
                                this.autoDetectLocation();
                            });
                        }
                    });
                    this.$watch('formData.address.street', () => this.scheduleForwardGeocode());
                    this.$watch('formData.address.city', () => this.scheduleForwardGeocode());
                    this.$watch('formData.address.state', () => this.scheduleForwardGeocode());
                    this.$watch('formData.address.postal_code', () => this.scheduleForwardGeocode());
                    this.$watch('formData.country', () => this.scheduleForwardGeocode());
                },
                flagFromIso(iso) {
                    const cc = String(iso || '').toUpperCase();
                    if (!cc || cc.length !== 2) return '';
                    const A = 127397;
                    return String.fromCodePoint(A + cc.charCodeAt(0)) + String.fromCodePoint(A + cc.charCodeAt(1));
                },
                flagUrlFromIso(iso, size = '24x18') {
                    const cc = String(iso || '').toLowerCase();
                    if (!cc || cc.length !== 2) return '';
                    return `https://flagcdn.com/${size}/${cc}.png`;
                },
                updatePhoneCodeFromCountry() {
                    if (this.formData.country) {
                        const selectedCountry = this.countries.find(c => c.name === this.formData.country);
                        if (selectedCountry) {
                            this.formData.country_code = selectedCountry.phone;
                        }
                    }
                },
                sanitizePhone() {
                    const digits = String(this.formData.phone || '').replace(/\D/g, '');
                    const max = this.phoneMax;
                    this.formData.phone = digits.slice(0, max);
                },
                get phoneMax() {
                    const code = String(this.formData.country_code || '');
                    return code.startsWith('+91') ? 10 : 15;
                },
                evaluatePassword() {
                    const pw = this.formData.password || '';
                    const checks = {
                        lowercase: /[a-z]/.test(pw),
                        uppercase: /[A-Z]/.test(pw),
                        number: /[0-9]/.test(pw),
                        special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(pw),
                        length: pw.length >= 8 && pw.length <= 20,
                        noCommon: !['password', 'password123', '12345678', 'qwerty', 'letmein', 'admin', 'welcome'].includes(pw.toLowerCase())
                    };
                    let score = 0;
                    if (checks.lowercase) score += 15;
                    if (checks.uppercase) score += 15;
                    if (checks.number) score += 15;
                    if (checks.special) score += 20;
                    if (checks.length) score += 20;
                    if (checks.noCommon) score += 15;
                    if (pw.length >= 12) score += 5;
                    if (pw.length >= 16) score += 5;
                    if (score < 30) {
                        this.passwordStrengthText = 'Very Weak';
                        this.passwordStrengthTextClass = 'text-red-600';
                        this.passwordStrengthBarClass = 'bg-red-500';
                        this.passwordStrengthBarStyle = 'width: 20%';
                    } else if (score < 50) {
                        this.passwordStrengthText = 'Weak';
                        this.passwordStrengthTextClass = 'text-orange-600';
                        this.passwordStrengthBarClass = 'bg-orange-500';
                        this.passwordStrengthBarStyle = 'width: 40%';
                    } else if (score < 70) {
                        this.passwordStrengthText = 'Fair';
                        this.passwordStrengthTextClass = 'text-yellow-600';
                        this.passwordStrengthBarClass = 'bg-yellow-500';
                        this.passwordStrengthBarStyle = 'width: 60%';
                    } else if (score < 90) {
                        this.passwordStrengthText = 'Good';
                        this.passwordStrengthTextClass = 'text-green-600';
                        this.passwordStrengthBarClass = 'bg-green-500';
                        this.passwordStrengthBarStyle = 'width: 80%';
                    } else {
                        this.passwordStrengthText = 'Strong';
                        this.passwordStrengthTextClass = 'text-green-700';
                        this.passwordStrengthBarClass = 'bg-green-600';
                        this.passwordStrengthBarStyle = 'width: 100%';
                    }
                    this.passwordValid = Object.values(checks).every(Boolean);
                    this.passwordError = this.passwordValid || pw.length === 0 ? '' : 'Password does not meet all requirements';
                    this.passwordSuggestions = [];
                    if (!checks.lowercase) this.passwordSuggestions.push('Add lowercase letters (a-z)');
                    if (!checks.uppercase) this.passwordSuggestions.push('Add uppercase letters (A-Z)');
                    if (!checks.number) this.passwordSuggestions.push('Add numbers (0-9)');
                    if (!checks.special) this.passwordSuggestions.push('Add special characters (!@#$%^&*)');
                    if (!checks.length) this.passwordSuggestions.push('Use 8–20 characters');
                    if (!checks.noCommon) this.passwordSuggestions.push('Avoid common passwords');
                },
                handleFileUpload(event, type) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File size must be less than 2MB');
                            event.target.value = '';
                            return;
                        }
                        const fileData = {
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            file: file,
                            preview: null
                        };
                        
                        // Create preview for images and PDFs
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                fileData.preview = e.target.result;
                                this.formData.documents[type] = fileData;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            // For PDFs, create object URL for iframe preview
                            fileData.preview = null;
                            fileData.previewURL = URL.createObjectURL(file);
                            this.formData.documents[type] = fileData;
                        }
                    } else {
                        this.formData.documents[type] = null;
                    }
                },
                dragOver(event) {
                    event.currentTarget.classList.add('active');
                    event.currentTarget.classList.add('accept');
                },
                dragLeave(event) {
                    event.currentTarget.classList.remove('active');
                    event.currentTarget.classList.remove('accept');
                },
                dropFile(event, type) {
                    event.currentTarget.classList.remove('active');
                    event.currentTarget.classList.remove('accept');
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File size must be less than 2MB');
                            return;
                        }
                        const fileData = {
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            file: file,
                            preview: null
                        };
                        
                        // Create preview for images and PDFs
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                fileData.preview = e.target.result;
                                this.formData.documents[type] = fileData;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            // For PDFs, create object URL for iframe preview
                            fileData.preview = null;
                            fileData.previewURL = URL.createObjectURL(file);
                            this.formData.documents[type] = fileData;
                        }
                    }
                },
                openPreview(type) {
                    const doc = this.formData.documents[type];
                    if (!doc || !doc.file) return;
                    
                    // Ensure previewURL exists
                    if (!doc.previewURL && !doc.preview) {
                        if (doc.file.type.startsWith('image/')) {
                            // For images, create data URL
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                doc.preview = e.target.result;
                            };
                            reader.readAsDataURL(doc.file);
                        } else {
                            // For PDFs, create object URL
                            doc.previewURL = URL.createObjectURL(doc.file);
                        }
                    }
                },
                removeDocument(type) {
                    const doc = this.formData.documents[type];
                    if (doc && doc.previewURL) {
                        URL.revokeObjectURL(doc.previewURL);
                    }
                    this.formData.documents[type] = null;
                },
                async saveStep(step) {
                    this.isSaving = true;
                    try {
                        // Save form data to localStorage
                        const savedData = {
                            step: step,
                            formData: JSON.parse(JSON.stringify(this.formData)),
                            timestamp: new Date().toISOString()
                        };
                        
                        // Remove file objects before saving to localStorage (they can't be serialized)
                        if (savedData.formData.documents) {
                            Object.keys(savedData.formData.documents).forEach(key => {
                                if (savedData.formData.documents[key] && savedData.formData.documents[key].file) {
                                    savedData.formData.documents[key] = {
                                        name: savedData.formData.documents[key].name,
                                        size: savedData.formData.documents[key].size,
                                        type: savedData.formData.documents[key].type
                                    };
                                }
                            });
                        }
                        
                        localStorage.setItem('employer_registration_draft', JSON.stringify(savedData));
                        
                        // Show success message
                        alert('Progress saved successfully! You can continue later.');
                        this.isSaving = false;
                    } catch (error) {
                        console.error('Error saving step:', error);
                        alert('Failed to save progress. Please try again.');
                        this.isSaving = false;
                    }
                },
                loadSavedData() {
                    try {
                        const saved = localStorage.getItem('employer_registration_draft');
                        if (saved) {
                            const savedData = JSON.parse(saved);
                            if (savedData.formData && confirm('Found saved progress. Would you like to continue from where you left off?')) {
                                // Merge saved data with current form data
                                Object.keys(savedData.formData).forEach(key => {
                                    if (key !== 'documents') {
                                        this.formData[key] = savedData.formData[key] || this.formData[key];
                                    }
                                });
                                this.currentStep = savedData.step || 1;
                            }
                        }
                    } catch (error) {
                        console.error('Error loading saved data:', error);
                    }
                },
                initMap() {
                    if (this.map) return;
                    this.map = L.map('employer-map').setView([20.0, 0.0], 2);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(this.map);
                    this.marker = L.marker([20.0, 0.0], {
                        draggable: true
                    }).addTo(this.map);
                    this.marker.on('dragend', () => {
                        const pos = this.marker.getLatLng();
                        this.formData.address.lat = pos.lat;
                        this.formData.address.lng = pos.lng;
                        this.reverseGeocode(pos.lat, pos.lng);
                    });
                },
                autoDetectLocation() {
                    if (!confirm('We want to know your location to ensure employer authenticity. Allow location access?')) {
                        return;
                    }
                    if (!navigator.geolocation) return;
                    navigator.geolocation.getCurrentPosition((position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        this.formData.address.lat = lat;
                        this.formData.address.lng = lng;
                        if (this.map && this.marker) {
                            this.map.setView([lat, lng], 15);
                            this.marker.setLatLng([lat, lng]);
                        }
                        this.reverseGeocode(lat, lng);
                    }, () => {}, {
                        enableHighAccuracy: true,
                        timeout: 8000
                    });
                },
                reverseGeocode(lat, lng) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            const addr = data.address || {};
                            this.formData.address.street = addr.road || this.formData.address.street;
                            this.formData.address.city = addr.city || addr.town || addr.village || this.formData.address.city;
                            this.formData.address.state = addr.state || this.formData.address.state;
                            this.formData.address.postal_code = addr.postcode || this.formData.address.postal_code;
                            const countryName = addr.country || '';
                            if (countryName) {
                                this.formData.country = countryName;
                                this.updatePhoneCodeFromCountry();
                            }
                        })
                        .catch(() => {});
                },
                updateCountryType() {
                    if (this.formData.country === 'India') {
                        this.formData.company_type = 'national';
                    } else {
                        this.formData.company_type = 'international';
                    }
                },
                scheduleForwardGeocode() {
                    if (this.currentStep !== 2) return;
                    if (this.geocodeTimer) clearTimeout(this.geocodeTimer);
                    this.geocodeTimer = setTimeout(() => this.forwardGeocode(), 600);
                },
                forwardGeocode() {
                    const parts = [];
                    if (this.formData.address.street) parts.push(this.formData.address.street);
                    if (this.formData.address.city) parts.push(this.formData.address.city);
                    if (this.formData.address.state) parts.push(this.formData.address.state);
                    if (this.formData.address.postal_code) parts.push(this.formData.address.postal_code);
                    if (this.formData.country) parts.push(this.formData.country);
                    const q = encodeURIComponent(parts.join(', ').trim());
                    if (!q) return;
                    fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${q}`)
                        .then(res => res.json())
                        .then(results => {
                            if (Array.isArray(results) && results.length) {
                                const lat = parseFloat(results[0].lat);
                                const lng = parseFloat(results[0].lon);
                                this.formData.address.lat = lat;
                                this.formData.address.lng = lng;
                                if (this.map && this.marker && !isNaN(lat) && !isNaN(lng)) {
                                    this.map.setView([lat, lng], 15);
                                    this.marker.setLatLng([lat, lng]);
                                }
                            }
                        })
                        .catch(() => {});
                },
                async submitRegistration() {
                    this.isSubmitting = true;
                    try {
                        if (!this.formData.accept_terms) {
                            alert('Please accept the terms and conditions');
                            this.isSubmitting = false;
                            return;
                        }
                        if (!this.formData.address.lat || !this.formData.address.lng) {
                            alert('Please pin your exact location on the map');
                            this.isSubmitting = false;
                            return;
                        }
                        if (!this.formData.documents.business_license || !this.formData.documents.business_license.file ||
                            !this.formData.documents.tax_id || !this.formData.documents.tax_id.file ||
                            !this.formData.documents.address_proof || !this.formData.documents.address_proof.file) {
                            alert('Please upload all required documents');
                            this.isSubmitting = false;
                            return;
                        }
                        if (this.formData.company_type === 'international' &&
                            (!this.formData.documents.director_id || !this.formData.documents.director_id.file)) {
                            alert('Director ID is required for international companies');
                            this.isSubmitting = false;
                            return;
                        }
                        const formData = new FormData();
                        formData.append('email', this.formData.email);
                        formData.append('password', this.formData.password);
                        formData.append('role', 'employer');
                        formData.append('company_name', this.formData.company_name);
                        formData.append('phone', this.formData.phone);
                        formData.append('country_code', this.formData.country_code);
                        formData.append('country', this.formData.country);
                        formData.append('company_type', this.formData.company_type);
                        formData.append('website', this.formData.website);
                        formData.append('description', this.formData.description);
                        formData.append('industry', this.formData.industry);
                        formData.append('company_size', this.formData.company_size);
                        formData.append('address', JSON.stringify(this.formData.address));
                        formData.append('tax_id', this.formData.tax_id);
                        const csrfToken = this.getCsrfToken();
                        if (!csrfToken) {
                            alert('CSRF token not found. Please refresh the page and try again.');
                            this.isSubmitting = false;
                            return;
                        }
                        formData.append('_token', csrfToken);
                        if (this.formData.documents.business_license && this.formData.documents.business_license.file) {
                            formData.append('doc_business_license', this.formData.documents.business_license.file);
                        }
                        if (this.formData.documents.tax_id && this.formData.documents.tax_id.file) {
                            formData.append('doc_tax_id', this.formData.documents.tax_id.file);
                        }
                        if (this.formData.documents.address_proof && this.formData.documents.address_proof.file) {
                            formData.append('doc_address_proof', this.formData.documents.address_proof.file);
                        }
                        if (this.formData.documents.director_id && this.formData.documents.director_id.file) {
                            formData.append('doc_director_id', this.formData.documents.director_id.file);
                        }
                        if (this.formData.documents.other && this.formData.documents.other.file) {
                            formData.append('doc_other', this.formData.documents.other.file);
                        }
                        const response = await fetch('/register-employer', {
                            method: 'POST',
                            body: formData
                        });
                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            alert('Registration failed: Invalid server response.');
                            this.isSubmitting = false;
                            return;
                        }
                        if (response.ok && data.success) {
                            const redirectUrl = data.redirect || '/employer/dashboard';
                            window.location.href = redirectUrl;
                        } else {
                            const errorMsg = data.error || data.message || data.errors || 'Registration failed';
                            alert('Error: ' + (typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg));
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                getCsrfToken() {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (!meta) return '';
                    const token = meta.getAttribute('content');
                    return token || '';
                }
            }
        };
    </script>
</head>

<body class="bg-gray-50">
    <div x-data="window.registrationForm()" x-cloak class="min-h-screen flex flex-col">
        <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>
        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 md:px-16 py-12 w-full flex">
                <!-- Left Sidebar -->
                <div class="w-64 pr-8 hidden lg:block">
                    <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Registration Steps</h2>
                        <div class="relative step-connector">
                            <div class="step-item mb-6" :class="{'text-green-600': currentStep === 1, 'text-gray-400': currentStep !== 1}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                        :class="{'bg-green-600 text-white': currentStep === 1, 'bg-gray-100 text-gray-400': currentStep !== 1}">
                                        <i class="fas fa-user-tie text-xs"></i>
                                    </div>
                                    <span class="font-medium">Basic Information</span>
                                </div>
                            </div>
                            <div class="step-item mb-6" :class="{'text-green-600': currentStep === 2, 'text-gray-400': currentStep !== 2}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                        :class="{'bg-green-600 text-white': currentStep === 2, 'bg-gray-100 text-gray-400': currentStep !== 2}">
                                        <i class="fas fa-map-marker-alt text-xs"></i>
                                    </div>
                                    <span class="font-medium">Address Information</span>
                                </div>
                            </div>
                            <div class="step-item" :class="{'text-green-600': currentStep === 3, 'text-gray-400': currentStep !== 3}">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                        :class="{'bg-green-600 text-white': currentStep === 3, 'bg-gray-100 text-gray-400': currentStep !== 3}">
                                        <i class="fas fa-file-alt text-xs"></i>
                                    </div>
                                    <span class="font-medium">Document Verifications</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Form Content -->
                <div class="flex-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
                        <div class="mb-8 text-center">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Employer Registration</h1>
                            <p class="text-gray-600">Create your employer account and start posting jobs</p>
                        </div>
                        <form @submit.prevent="submitRegistration()" class="space-y-8">
                            <!-- Step 1: Basic Information -->
                            <div x-show="currentStep === 1" x-transition>
                                <div class="mb-6">
                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center">
                                            <div class="w-full border-t border-gray-200"></div>
                                        </div>
                                        <div class="relative flex justify-center">
                                            <span class="px-3 bg-white text-xs text-gray-500">Or continue with</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-4 gap-3">
                                        <a href="/auth/google?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Google">
                                            <img alt="Google" class="h-6 w-6" src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png">
                                        </a>
                                        <!-- <a href="/auth/facebook?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Facebook">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="#1877F2" d="M24 12.073C24 5.403 18.627 0 12 0S0 5.403 0 12.073C0 18.09 4.388 23.092 10.125 24v-8.437H7.078V12.07h3.047V9.412c0-3.007 1.792-4.667 4.533-4.667 1.313 0 2.686.235 2.686.235v2.955h-1.513c-1.49 0-1.953.93-1.953 1.887v2.248h3.328l-.532 3.493h-2.796V24C19.612 23.092 24 18.09 24 12.073z"/>
                                                <path fill="#fff" d="M16.906 15.563l.532-3.493h-3.328V9.822c0-.957.463-1.887 1.953-1.887h1.513V4.98s-1.373-.235-2.686-.235c-2.741 0-4.533 1.66-4.533 4.667v2.658H7.078v3.055h3.047V24h3.984v-8.437h2.796z"/>
                                            </svg>
                                        </a> -->
                                        <a href="/auth/linkedin?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with LinkedIn">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                                <rect width="24" height="24" rx="4" fill="#0A66C2"/>
                                                <path fill="#fff" d="M6.21 9.03h2.61v8.16H6.21V9.03zm1.31-4.22c.84 0 1.52.68 1.52 1.52s-.68 1.52-1.52 1.52-1.52-.68-1.52-1.52.68-1.52 1.52-1.52zM10.28 9.03h2.5v1.12h.04c.35-.66 1.19-1.36 2.45-1.36 2.62 0 3.1 1.72 3.1 3.95v4.44h-2.6v-3.93c0-.94-.02-2.16-1.32-2.16-1.32 0-1.52 1.03-1.52 2.09v4H10.28V9.03z"/>
                                            </svg>
                                        </a>
                                        <a href="/auth/microsoft?redirect=/employer/dashboard" class="flex items-center justify-center rounded-md border border-green-300 bg-white hover:bg-green-50 p-2" aria-label="Continue with Microsoft">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" aria-hidden="true">
                                                <rect x="2" y="2" width="9" height="9" fill="#F25022"/>
                                                <rect x="13" y="2" width="9" height="9" fill="#7FBA00"/>
                                                <rect x="2" y="13" width="9" height="9" fill="#00A4EF"/>
                                                <rect x="13" y="13" width="9" height="9" fill="#FFB900"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-6 border border-green-100 mb-8">
                                    <h2 class="text-xl font-semibold text-green-800 mb-1 flex items-center">
                                        <i class="fas fa-info-circle mr-2"></i> Basic Information
                                    </h2>
                                    <p class="text-sm text-green-600">Tell us about your company</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-envelope text-gray-400"></i>
                                            </div>
                                            <input type="email" x-model="formData.email" required
                                                placeholder="your@email.com"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-lock text-gray-400"></i>
                                            </div>
                                            <input :type="showPassword ? 'text' : 'password'" x-model="formData.password" required
                                                placeholder="Create a strong password"
                                                :class="passwordValid ? 'w-full pl-10 px-4 py-3 border rounded-lg border-blue-600 focus:ring-2 focus:ring-blue-600 focus:border-blue-600' : (passwordError ? 'w-full pl-10 px-4 py-3 border rounded-lg border-red-500 focus:ring-2 focus:ring-red-500 focus:border-red-500' : 'w-full pl-10 px-4 py-3 border rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-blue-600')">
                                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500">
                                                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                        <div x-show="formData.password.length > 0" class="mt-2 space-y-2">
                                            <div class="h-2 rounded bg-gray-200 overflow-hidden">
                                                <div class="h-2" :class="passwordStrengthBarClass" :style="passwordStrengthBarStyle"></div>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="font-medium" :class="passwordStrengthTextClass" x-text="passwordStrengthText"></span>
                                                <span class="text-gray-500" x-text="formData.password.length + ' / 20 characters'"></span>
                                            </div>
                                            <div x-show="!passwordValid && formData.password.length > 0" class="mt-3 p-3 bg-green-50 border-l-4 border-green-500 rounded">
                                                <p class="text-sm text-gray-700">Password Suggestions:</p>
                                                <ul class="list-disc list-inside text-sm text-gray-600">
                                                    <template x-for="suggestion in passwordSuggestions" :key="suggestion">
                                                        <li x-text="suggestion"></li>
                                                    </template>
                                                </ul>
                                            </div>
                                            <p x-show="passwordError" class="mt-1 text-sm text-red-600" x-text="passwordError"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    
                                    <div x-data="{open:false, q:'', hover:-1}" @keydown.escape.window="open=false">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <button type="button" @click="open=!open" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white flex items-center justify-between">
                                                <span class="flex items-center gap-2">
                                                    <template x-if="countries.find(c => c.name === formData.country)">
                                                        <img :src="flagUrlFromIso(countries.find(c => c.name === formData.country)?.code || '')" width="24" height="18" class="inline-block rounded-sm border border-gray-200">
                                                    </template>
                                                    <span x-text="formData.country || 'Select Country'"></span>
                                                </span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z" clip-rule="evenodd"/></svg>
                                            </button>
                                            <div x-show="open" x-transition class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow">
                                                <div class="p-2">
                                                    <input type="text" x-model="q" placeholder="Search country" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                                </div>
                                                <ul class="max-h-60 overflow-auto">
                                                    <template x-for="(country, idx) in countries.filter(c => c.name.toLowerCase().includes(q.toLowerCase()))" :key="country.name">
                                                        <li @mouseenter="hover=idx" @mouseleave="hover=-1" @click="formData.country = country.name; open=false; updateCountryType(); updatePhoneCodeFromCountry()"
                                                            :class="hover===idx ? 'bg-gray-50' : ''"
                                                            class="px-3 py-2 cursor-pointer flex items-center gap-2">
                                                            <img :src="flagUrlFromIso(country.code || '')" width="20" height="15" class="rounded-sm border border-gray-200">
                                                            <span class="text-sm" x-text="country.name"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                            <select x-model="formData.country" required class="hidden">
                                                <option value="">Select Country</option>
                                                <template x-for="country in countries" :key="country.name">
                                                    <option :value="country.name" x-text="country.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Company Type <span class="text-red-500">*</span>
                                        </label>
    
                                        <select
                                            x-model="formData.company_type"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                                            required>
                                            <option value="" disabled selected>Select Company Type</option>
    
                                            <option value="proprietorship">Proprietorship</option>
                                            <option value="partnership">Partnership</option>
                                            <option value="private_limited">Private Limited</option>
                                            <option value="public_limited">Public Limited</option>
                                            <option value="llp">Limited Liability Partnership (LLP)</option>
                                            <option value="opc">One Person Company (OPC)</option>
                                            <option value="government">Government / PSU</option>
                                            <option value="non_profit">Non-Profit (NGO / Trust)</option>
                                            <option value="startup">Startup</option>
                                            <option value="freelancer">Freelancer / Individual</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-building text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.company_name" required
                                                placeholder="Your Company Name"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                                        <div class="flex">
                                            <div class="relative">
                                                <button type="button" @click="phoneCodeOpen = !phoneCodeOpen" class="px-3 py-3 border border-gray-300 rounded-l-lg bg-gray-50 w-48 flex items-center gap-2">
                                                    <template x-if="countries.find(c => c.phone === formData.country_code)">
                                                        <img :src="flagUrlFromIso(countries.find(c => c.phone === formData.country_code)?.code || '')" width="20" height="15" class="rounded-sm border border-gray-200">
                                                    </template>
                                                    <span class="text-sm" x-text="formData.country_code || '+Code'"></span>
                                                </button>
                                                <div x-show="phoneCodeOpen" x-transition @click.away="phoneCodeOpen = false" @keydown.escape.window="phoneCodeOpen = false" class="absolute z-50 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow">
                                                    <div class="p-2">
                                                        <input type="text" x-model="phoneCodeSearch" placeholder="Search code or country" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                                    </div>
                                                    <ul class="max-h-60 overflow-auto">
                                                        <template x-for="country in countries.filter(c => (c.name.toLowerCase().includes(phoneCodeSearch.toLowerCase()) || (c.phone || '').includes(phoneCodeSearch)))" :key="country.name">
                                                            <li @click="formData.country_code = country.phone; phoneCodeOpen = false; updatePhoneCodeFromCountry()"
                                                                class="px-3 py-2 cursor-pointer flex items-center gap-2 hover:bg-gray-50">
                                                                <img :src="flagUrlFromIso(country.code || '')" width="20" height="15" class="rounded-sm border border-gray-200">
                                                                <span class="text-sm" x-text="(country.phone || '') + ' (' + country.name + ')'"></span>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </div>
                                                <select x-model="formData.country_code" class="hidden">
                                                    <template x-for="country in countries" :key="country.name">
                                                        <option :value="country.phone" x-text="country.phone"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <input type="tel" x-model="formData.phone" required
                                                placeholder="Phone number"
                                                class="flex-1 px-4 py-3 border border-gray-300 rounded-r-lg -ml-px focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition"
                                                x-on:input="sanitizePhone()"
                                                :maxlength="String(formData.country_code || '').startsWith('+91') ? 10 : 15"
                                                inputmode="numeric">
                                        </div>
                                    </div>
                                </div>
                                

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Website</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-globe text-gray-400"></i>
                                            </div>
                                            <input type="url" x-model="formData.website"
                                                placeholder="https://www.company.com"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Company Size <span class="text-red-500">*</span></label>
                                        <select x-model="formData.company_size" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                            <option value="">Select Size</option>
                                            <option value="1-10">1-10 employees</option>
                                            <option value="11-50">11-50 employees</option>
                                            <option value="51-200">51-200 employees</option>
                                            <option value="201-500">201-500 employees</option>
                                            <option value="501-1000">501-1000 employees</option>
                                            <option value="1001+">1001+ employees</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Industry Type</label>
                                    <select x-model="formData.industry"
                                        class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
                                        <option value="">Select Industry</option>
                                        <option value="IT/Software">IT/Software</option>
                                        <option value="Finance">Finance</option>
                                        <option value="Healthcare">Healthcare</option>
                                        <option value="Education">Education</option>
                                        <option value="Manufacturing">Manufacturing</option>
                                        <option value="Retail">Retail</option>
                                        <option value="Real Estate">Real Estate</option>
                                        <option value="Hospitality">Hospitality</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Company Description</label>
                                    <textarea x-model="formData.description" rows="4"
                                        placeholder="Brief description about your company..."
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition"></textarea>
                                </div>
                                <div class="flex justify-between">
                                    <button type="button" @click="saveStep(1)"
                                        :disabled="isSaving"
                                        class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 transition flex items-center">
                                        <i class="fas fa-save mr-1"></i>
                                        <span x-show="!isSaving">Save</span>
                                        <span x-show="isSaving">Saving...</span>
                                    </button>
                                    <button type="button" @click="currentStep = 2"
                                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                                        <i class="fas fa-arrow-right mr-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Step 2: Address Information -->
                            <div x-show="currentStep === 2" x-transition>
                                <div class="bg-green-50 rounded-lg p-6 border border-green-100 mb-8">
                                    <h2 class="text-xl font-semibold text-green-800 mb-1 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-2"></i> Address Information
                                    </h2>
                                    <p class="text-sm text-green-600">Where is your company located?</p>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-map-marked-alt text-gray-400"></i>
                                        </div>
                                        <input type="text" x-model="formData.address.street" required
                                            placeholder="Street address"
                                            class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-city text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.address.city" required
                                                placeholder="City"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">State/Province <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-flag text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.address.state" required
                                                placeholder="State"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-mail-bulk text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="formData.address.postal_code" required
                                                placeholder="Postal code"
                                                class="w-full pl-10 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-6">
                                    <div class="flex justify-between mb-2">
                                        <button type="button" @click="autoDetectLocation()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            Use my location
                                        </button>
                                        <div class="text-sm text-gray-500" x-show="formData.address.lat && formData.address.lng">
                                            <span>Lat: </span><span x-text="formData.address.lat && formData.address.lat.toFixed ? formData.address.lat.toFixed(5) : formData.address.lat"></span>,
                                            <span>Lng: </span><span x-text="formData.address.lng && formData.address.lng.toFixed ? formData.address.lng.toFixed(5) : formData.address.lng"></span>
                                        </div>
                                    </div>
                                    <div id="employer-map" class="w-full h-64 rounded-lg border border-gray-200"></div>
                                    <p class="mt-2 text-sm text-gray-600">Drag the marker to your exact location. Address will update automatically.</p>
                                </div>
                                <div class="flex justify-between">
                                    <div class="flex gap-3">
                                        <button type="button" @click="currentStep = 1"
                                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center">
                                            <i class="fas fa-arrow-left mr-1"></i> Back
                                        </button>
                                        <button type="button" @click="saveStep(2)"
                                            :disabled="isSaving"
                                            class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 transition flex items-center">
                                            <i class="fas fa-save mr-1"></i>
                                            <span x-show="!isSaving">Save</span>
                                            <span x-show="isSaving">Saving...</span>
                                        </button>
                                    </div>
                                    <button type="button" @click="currentStep = 3"
                                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center">
                                        <i class="fas fa-arrow-right mr-1"></i> Next
                                    </button>
                                </div>
                            </div>
                            <!-- Step 3: KYC Documentation -->
                            <div x-show="currentStep === 3" x-transition>
                                <div class="bg-green-50 rounded-lg p-6 border border-green-100 mb-8">
                                    <h2 class="text-xl font-semibold text-green-800 mb-1 flex items-center">
                                        <i class="fas fa-file-alt mr-2"></i> Document Verifications
                                    </h2>
                                    <p class="text-sm text-green-600">Upload required documents for verification</p>
                                </div>
                                <div class="space-y-6">
                                    <!-- Business License -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-file-contract text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Business License / Registration Certificate <span class="text-red-500">* 2 MB Only</span></h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.businessLicense.click()"
                                            @dragover.prevent="dragOver($event, 'business_license')"
                                            @dragleave.prevent="dragLeave($event, 'business_license')"
                                            @drop.prevent="dropFile($event, 'business_license')">
                                            <input type="file" @change="handleFileUpload($event, 'business_license')" x-ref="businessLicense" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.business_license">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">PDF, JPG, PNG (Max 2MB)</p>
                                            </template>
                                            <template x-if="formData.documents.business_license">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.business_license.preview" 
                                                             :src="formData.documents.business_license.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.business_license.preview && formData.documents.business_license.previewURL" 
                                                                :src="formData.documents.business_license.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.business_license.preview && !formData.documents.business_license.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.business_license.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.business_license.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.business_license.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('business_license')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('business_license')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Tax ID -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-file-invoice text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Tax ID / GST Number <span class="text-red-500">* 2 MB ONLY</span></h3>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">GST Number / Tax ID</label>
                                            <input type="text" x-model="formData.tax_id"
                                                placeholder="GST Number / Tax ID"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition">
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.taxId.click()"
                                            @dragover.prevent="dragOver($event, 'tax_id')"
                                            @dragleave.prevent="dragLeave($event, 'tax_id')"
                                            @drop.prevent="dropFile($event, 'tax_id')">
                                            <input type="file" @change="handleFileUpload($event, 'tax_id')" x-ref="taxId" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.tax_id">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">PDF, JPG, PNG (Max 2MB)</p>
                                            </template>
                                            <template x-if="formData.documents.tax_id">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.tax_id.preview" 
                                                             :src="formData.documents.tax_id.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.tax_id.preview && formData.documents.tax_id.previewURL" 
                                                                :src="formData.documents.tax_id.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.tax_id.preview && !formData.documents.tax_id.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.tax_id.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.tax_id.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.tax_id.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('tax_id')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('tax_id')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Address Proof -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-home text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Address Proof <span class="text-red-500">* 2 MB ONLY</span></h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.addressProof.click()"
                                            @dragover.prevent="dragOver($event, 'address_proof')"
                                            @dragleave.prevent="dragLeave($event, 'address_proof')"
                                            @drop.prevent="dropFile($event, 'address_proof')">
                                            <input type="file" @change="handleFileUpload($event, 'address_proof')" x-ref="addressProof" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.address_proof">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">Utility bill, Bank statement, etc. (PDF, JPG, PNG) Max 2MB</p>
                                            </template>
                                            <template x-if="formData.documents.address_proof">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.address_proof.preview" 
                                                             :src="formData.documents.address_proof.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.address_proof.preview && formData.documents.address_proof.previewURL" 
                                                                :src="formData.documents.address_proof.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.address_proof.preview && !formData.documents.address_proof.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.address_proof.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.address_proof.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.address_proof.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('address_proof')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('address_proof')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Director ID (for International) -->
                                    <div x-show="formData.company_type === 'international'" class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-id-card text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Director/Authorized Person ID <span class="text-red-500">*</span></h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.directorId.click()"
                                            @dragover.prevent="dragOver($event, 'director_id')"
                                            @dragleave.prevent="dragLeave($event, 'director_id')"
                                            @drop.prevent="dropFile($event, 'director_id')">
                                            <input type="file" @change="handleFileUpload($event, 'director_id')" x-ref="directorId" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.director_id">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">Passport, National ID, etc. (PDF, JPG, PNG) Max 2MB</p>
                                            </template>
                                            <template x-if="formData.documents.director_id">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.director_id.preview" 
                                                             :src="formData.documents.director_id.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.director_id.preview && formData.documents.director_id.previewURL" 
                                                                :src="formData.documents.director_id.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.director_id.preview && !formData.documents.director_id.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.director_id.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.director_id.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.director_id.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('director_id')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('director_id')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <!-- Additional Documents -->
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-paperclip text-green-500 mr-2"></i>
                                            <h3 class="font-medium text-gray-800">Additional Documents (Optional)</h3>
                                        </div>
                                        <div class="file-upload-area p-6 text-center cursor-pointer"
                                            @click="$refs.otherDoc.click()"
                                            @dragover.prevent="dragOver($event, 'other')"
                                            @dragleave.prevent="dragLeave($event, 'other')"
                                            @drop.prevent="dropFile($event, 'other')">
                                            <input type="file" @change="handleFileUpload($event, 'other')" x-ref="otherDoc" style="display: none;"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <template x-if="!formData.documents.other">
                                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 mb-2"></i>
                                                <p class="text-gray-500 mb-1">Drag & drop files here or click to browse</p>
                                                <p class="text-xs text-gray-400">Any other relevant documents</p>
                                            </template>
                                            <template x-if="formData.documents.other">
                                                <div class="w-full">
                                                    <div class="document-preview-container mb-3">
                                                        <img x-show="formData.documents.other.preview" 
                                                             :src="formData.documents.other.preview" 
                                                             alt="Preview" 
                                                             class="document-preview-image">
                                                        <iframe x-show="!formData.documents.other.preview && formData.documents.other.previewURL" 
                                                                :src="formData.documents.other.previewURL" 
                                                                class="document-preview-iframe"></iframe>
                                                        <div x-show="!formData.documents.other.preview && !formData.documents.other.previewURL" 
                                                             class="flex flex-col items-center justify-center py-8">
                                                            <i class="fas fa-file-pdf text-5xl text-red-500 mb-2"></i>
                                                            <p class="text-gray-700 font-medium mb-1" x-text="formData.documents.other.name"></p>
                                                            <p class="text-xs text-gray-500">Click Preview to view</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                        <div class="flex-1">
                                                            <p class="text-sm text-gray-700 font-medium" x-text="formData.documents.other.name"></p>
                                                            <p class="text-xs text-gray-500">Size: <span x-text="(formData.documents.other.size/1048576).toFixed(2)+' MB'"></span></p>
                                                        </div>
                                                        <div class="flex gap-2">
                                                            <button type="button" 
                                                                    @click="openPreview('other')" 
                                                                    class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-900 transition">
                                                                <i class="fas fa-eye mr-1"></i>Preview
                                                            </button>
                                                            <button type="button" 
                                                                    @click="removeDocument('other')" 
                                                                    class="px-3 py-1.5 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <!-- Terms and Conditions -->
                                <div class="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="flex items-start">
                                        <input type="checkbox" x-model="formData.accept_terms" required
                                            class="mt-1 mr-2 text-green-600">
                                        <span class="text-sm text-gray-700">
                                            I agree to the <a href="#" class="text-green-600 hover:underline">Terms and Conditions</a>
                                            and <a href="#" class="text-green-600 hover:underline">Privacy Policy</a> <span class="text-red-500">*</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="flex justify-between mt-8">
                                    <div class="flex gap-3">
                                        <button type="button" @click="currentStep = 2"
                                            class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center">
                                            <i class="fas fa-arrow-left mr-1"></i> Back
                                        </button>
                                        <button type="button" @click="saveStep(3)"
                                            :disabled="isSaving"
                                            class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 transition flex items-center">
                                            <i class="fas fa-save mr-1"></i>
                                            <span x-show="!isSaving">Save</span>
                                            <span x-show="isSaving">Saving...</span>
                                        </button>
                                    </div>
                                    <button type="submit"
                                        :disabled="isSubmitting"
                                        class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition flex items-center">
                                        <span x-show="!isSubmitting">Register & Submit Documents</span>
                                        <span x-show="isSubmitting">Submitting...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.countriesData = [{
                name: 'India',
                code: 'IN',
                phone: '+91'
            },
            {
                name: 'United States',
                code: 'US',
                phone: '+1'
            },
            {
                name: 'United Kingdom',
                code: 'GB',
                phone: '+44'
            },
            {
                name: 'Australia',
                code: 'AU',
                phone: '+61'
            },
            {
                name: 'United Arab Emirates',
                code: 'AE',
                phone: '+971'
            },
            {
                name: 'Singapore',
                code: 'SG',
                phone: '+65'
            },
            {
                name: 'Canada',
                code: 'CA',
                phone: '+1'
            },
            {
                name: 'Germany',
                code: 'DE',
                phone: '+49'
            },
            {
                name: 'France',
                code: 'FR',
                phone: '+33'
            },
            {
                name: 'Japan',
                code: 'JP',
                phone: '+81'
            },
            {
                name: 'Brazil',
                code: 'BR',
                phone: '+55'
            },
            {
                name: 'South Africa',
                code: 'ZA',
                phone: '+27'
            },
            {
                name: 'Russia',
                code: 'RU',
                phone: '+7'
            },
            {
                name: 'China',
                code: 'CN',
                phone: '+86'
            },
            {
                name: 'Egypt',
                code: 'EG',
                phone: '+20'
            },
            {
                name: 'Nigeria',
                code: 'NG',
                phone: '+234'
            },
            {
                name: 'Mexico',
                code: 'MX',
                phone: '+52'
            },
            {
                name: 'Italy',
                code: 'IT',
                phone: '+39'
            },
            {
                name: 'Spain',
                code: 'ES',
                phone: '+34'
            },
            {
                name: 'Netherlands',
                code: 'NL',
                phone: '+31'
            },
            {
                name: 'Sweden',
                code: 'SE',
                phone: '+46'
            },
            {
                name: 'Switzerland',
                code: 'CH',
                phone: '+41'
            },
            {
                name: 'Saudi Arabia',
                code: 'SA',
                phone: '+966'
            },
            {
                name: 'South Korea',
                code: 'KR',
                phone: '+82'
            },
            {
                name: 'Turkey',
                code: 'TR',
                phone: '+90'
            },
            {
                name: 'Argentina',
                code: 'AR',
                phone: '+54'
            },
            {
                name: 'Thailand',
                code: 'TH',
                phone: '+66'
            },
            {
                name: 'Malaysia',
                code: 'MY',
                phone: '+60'
            },
            {
                name: 'Indonesia',
                code: 'ID',
                phone: '+62'
            },
            {
                name: 'Philippines',
                code: 'PH',
                phone: '+63'
            },
            {
                name: 'Vietnam',
                code: 'VN',
                phone: '+84'
            },
            {
                name: 'Pakistan',
                code: 'PK',
                phone: '+92'
            },
            {
                name: 'Bangladesh',
                code: 'BD',
                phone: '+880'
            },
            {
                name: 'New Zealand',
                code: 'NZ',
                phone: '+64'
            },
            {
                name: 'Norway',
                code: 'NO',
                phone: '+47'
            },
            {
                name: 'Denmark',
                code: 'DK',
                phone: '+45'
            },
            {
                name: 'Finland',
                code: 'FI',
                phone: '+358'
            },
            {
                name: 'Belgium',
                code: 'BE',
                phone: '+32'
            },
            {
                name: 'Austria',
                code: 'AT',
                phone: '+43'
            },
            {
                name: 'Poland',
                code: 'PL',
                phone: '+48'
            },
            {
                name: 'Greece',
                code: 'GR',
                phone: '+30'
            },
            {
                name: 'Portugal',
                code: 'PT',
                phone: '+351'
            },
            {
                name: 'Ireland',
                code: 'IE',
                phone: '+353'
            },
            {
                name: 'Hungary',
                code: 'HU',
                phone: '+36'
            },
            {
                name: 'Czech Republic',
                code: 'CZ',
                phone: '+420'
            },
            {
                name: 'Ukraine',
                code: 'UA',
                phone: '+380'
            },
            {
                name: 'Romania',
                code: 'RO',
                phone: '+40'
            },
            {
                name: 'Israel',
                code: 'IL',
                phone: '+972'
            },
            {
                name: 'Qatar',
                code: 'QA',
                phone: '+974'
            },
            {
                name: 'Kuwait',
                code: 'KW',
                phone: '+965'
            },
            {
                name: 'Oman',
                code: 'OM',
                phone: '+968'
            },
            {
                name: 'Bahrain',
                code: 'BH',
                phone: '+973'
            },
            {
                name: 'Jordan',
                code: 'JO',
                phone: '+962'
            },
            {
                name: 'Lebanon',
                code: 'LB',
                phone: '+961'
            },
            {
                name: 'Morocco',
                code: 'MA',
                phone: '+212'
            },
            {
                name: 'Algeria',
                code: 'DZ',
                phone: '+213'
            },
            {
                name: 'Tunisia',
                code: 'TN',
                phone: '+216'
            },
            {
                name: 'Kenya',
                code: 'KE',
                phone: '+254'
            },
            {
                name: 'Ghana',
                code: 'GH',
                phone: '+233'
            },
            {
                name: 'Uganda',
                code: 'UG',
                phone: '+256'
            },
            {
                name: 'Tanzania',
                code: 'TZ',
                phone: '+255'
            },
            {
                name: 'Ethiopia',
                code: 'ET',
                phone: '+251'
            },
            {
                name: 'Zimbabwe',
                code: 'ZW',
                phone: '+263'
            },
            {
                name: 'Zambia',
                code: 'ZM',
                phone: '+260'
            },
            {
                name: 'Angola',
                code: 'AO',
                phone: '+244'
            },
            {
                name: 'Mozambique',
                code: 'MZ',
                phone: '+258'
            },
            {
                name: 'Botswana',
                code: 'BW',
                phone: '+267'
            },
            {
                name: 'Namibia',
                code: 'NA',
                phone: '+264'
            },
            {
                name: 'Malawi',
                code: 'MW',
                phone: '+265'
            },
            {
                name: 'Rwanda',
                code: 'RW',
                phone: '+250'
            },
            {
                name: 'Burundi',
                code: 'BI',
                phone: '+257'
            },
            {
                name: 'Cameroon',
                code: 'CM',
                phone: '+237'
            },
            {
                name: 'Ivory Coast',
                code: 'CI',
                phone: '+225'
            },
            {
                name: 'Senegal',
                code: 'SN',
                phone: '+221'
            },
            {
                name: 'Mali',
                code: 'ML',
                phone: '+223'
            },
            {
                name: 'Burkina Faso',
                code: 'BF',
                phone: '+226'
            },
            {
                name: 'Niger',
                code: 'NE',
                phone: '+227'
            },
            {
                name: 'Chad',
                code: 'TD',
                phone: '+235'
            },
            {
                name: 'Sudan',
                code: 'SD',
                phone: '+249'
            },
            {
                name: 'Libya',
                code: 'LY',
                phone: '+218'
            },
            {
                name: 'Mauritania',
                code: 'MR',
                phone: '+222'
            },
            {
                name: 'Liberia',
                code: 'LR',
                phone: '+231'
            },
            {
                name: 'Sierra Leone',
                code: 'SL',
                phone: '+232'
            },
            {
                name: 'Guinea',
                code: 'GN',
                phone: '+224'
            },
            {
                name: 'Togo',
                code: 'TG',
                phone: '+228'
            },
            {
                name: 'Benin',
                code: 'BJ',
                phone: '+229'
            },
            {
                name: 'Central African Republic',
                code: 'CF',
                phone: '+236'
            },
            {
                name: 'Congo (Brazzaville)',
                code: 'CG',
                phone: '+242'
            },
            {
                name: 'Congo (Kinshasa)',
                code: 'CD',
                phone: '+243'
            },
            {
                name: 'Gabon',
                code: 'GA',
                phone: '+241'
            },
            {
                name: 'Equatorial Guinea',
                code: 'GQ',
                phone: '+240'
            },
            {
                name: 'Djibouti',
                code: 'DJ',
                phone: '+253'
            },
            {
                name: 'Somalia',
                code: 'SO',
                phone: '+252'
            },
            {
                name: 'Eritrea',
                code: 'ER',
                phone: '+291'
            },
            {
                name: 'Comoros',
                code: 'KM',
                phone: '+269'
            },
            {
                name: 'Mauritius',
                code: 'MU',
                phone: '+230'
            },
            {
                name: 'Seychelles',
                code: 'SC',
                phone: '+248'
            },
            {
                name: 'Madagascar',
                code: 'MG',
                phone: '+261'
            },
            {
                name: 'Reunion',
                code: 'RE',
                phone: '+262'
            },
            {
                name: 'Mayotte',
                code: 'YT',
                phone: '+262'
            },
            {
                name: 'Cape Verde',
                code: 'CV',
                phone: '+238'
            },
            {
                name: 'Sao Tome and Principe',
                code: 'ST',
                phone: '+239'
            },
            {
                name: 'Gambia',
                code: 'GM',
                phone: '+220'
            },
            {
                name: 'Guinea-Bissau',
                code: 'GW',
                phone: '+245'
            },
            {
                name: 'Afghanistan',
                code: 'AF',
                phone: '+93'
            },
            {
                name: 'Albania',
                code: 'AL',
                phone: '+355'
            },
            {
                name: 'Andorra',
                code: 'AD',
                phone: '+376'
            },
            {
                name: 'Armenia',
                code: 'AM',
                phone: '+374'
            },
            {
                name: 'Azerbaijan',
                code: 'AZ',
                phone: '+994'
            },
            {
                name: 'Belarus',
                code: 'BY',
                phone: '+375'
            },
            {
                name: 'Bosnia and Herzegovina',
                code: 'BA',
                phone: '+387'
            },
            {
                name: 'Bulgaria',
                code: 'BG',
                phone: '+359'
            },
            {
                name: 'Croatia',
                code: 'HR',
                phone: '+385'
            },
            {
                name: 'Cyprus',
                code: 'CY',
                phone: '+357'
            },
            {
                name: 'Estonia',
                code: 'EE',
                phone: '+372'
            },
            {
                name: 'Georgia',
                code: 'GE',
                phone: '+995'
            },
            {
                name: 'Iceland',
                code: 'IS',
                phone: '+354'
            },
            {
                name: 'Kazakhstan',
                code: 'KZ',
                phone: '+7'
            },
            {
                name: 'Kosovo',
                code: 'XK',
                phone: '+383'
            },
            {
                name: 'Kyrgyzstan',
                code: 'KG',
                phone: '+996'
            },
            {
                name: 'Latvia',
                code: 'LV',
                phone: '+371'
            },
            {
                name: 'Lithuania',
                code: 'LT',
                phone: '+370'
            },
            {
                name: 'Luxembourg',
                code: 'LU',
                phone: '+352'
            },
            {
                name: 'Malta',
                code: 'MT',
                phone: '+356'
            },
            {
                name: 'Moldova',
                code: 'MD',
                phone: '+373'
            },
            {
                name: 'Monaco',
                code: 'MC',
                phone: '+377'
            },
            {
                name: 'Montenegro',
                code: 'ME',
                phone: '+382'
            },
            {
                name: 'North Macedonia',
                code: 'MK',
                phone: '+389'
            },
            {
                name: 'Serbia',
                code: 'RS',
                phone: '+381'
            },
            {
                name: 'Slovakia',
                code: 'SK',
                phone: '+421'
            },
            {
                name: 'Slovenia',
                code: 'SI',
                phone: '+386'
            },
            {
                name: 'Tajikistan',
                code: 'TJ',
                phone: '+992'
            },
            {
                name: 'Turkmenistan',
                code: 'TM',
                phone: '+993'
            },
            {
                name: 'Uzbekistan',
                code: 'UZ',
                phone: '+998'
            },
            {
                name: 'Vatican City',
                code: 'VA',
                phone: '+379'
            },
            {
                name: 'Yemen',
                code: 'YE',
                phone: '+967'
            },
            {
                name: 'Iraq',
                code: 'IQ',
                phone: '+964'
            },
            {
                name: 'Iran',
                code: 'IR',
                phone: '+98'
            },
            {
                name: 'Syria',
                code: 'SY',
                phone: '+963'
            },
            {
                name: 'Palestine',
                code: 'PS',
                phone: '+970'
            },
            {
                name: 'Nepal',
                code: 'NP',
                phone: '+977'
            },
            {
                name: 'Bhutan',
                code: 'BT',
                phone: '+975'
            },
            {
                name: 'Sri Lanka',
                code: 'LK',
                phone: '+94'
            },
            {
                name: 'Maldives',
                code: 'MV',
                phone: '+960'
            },
            {
                name: 'Brunei',
                code: 'BN',
                phone: '+673'
            },
            {
                name: 'Cambodia',
                code: 'KH',
                phone: '+855'
            },
            {
                name: 'Laos',
                code: 'LA',
                phone: '+856'
            },
            {
                name: 'Myanmar',
                code: 'MM',
                phone: '+95'
            },
            {
                name: 'Timor-Leste',
                code: 'TL',
                phone: '+670'
            },
            {
                name: 'Fiji',
                code: 'FJ',
                phone: '+679'
            },
            {
                name: 'Papua New Guinea',
                code: 'PG',
                phone: '+675'
            },
            {
                name: 'Solomon Islands',
                code: 'SB',
                phone: '+677'
            },
            {
                name: 'Vanuatu',
                code: 'VU',
                phone: '+678'
            },
            {
                name: 'Samoa',
                code: 'WS',
                phone: '+685'
            },
            {
                name: 'Tonga',
                code: 'TO',
                phone: '+676'
            },
            {
                name: 'Kiribati',
                code: 'KI',
                phone: '+686'
            },
            {
                name: 'Tuvalu',
                code: 'TV',
                phone: '+688'
            },
            {
                name: 'Nauru',
                code: 'NR',
                phone: '+674'
            },
            {
                name: 'Marshall Islands',
                code: 'MH',
                phone: '+692'
            },
            {
                name: 'Micronesia',
                code: 'FM',
                phone: '+691'
            },
            {
                name: 'Palau',
                code: 'PW',
                phone: '+680'
            },
            {
                name: 'Cook Islands',
                code: 'CK',
                phone: '+682'
            },
            {
                name: 'Niue',
                code: 'NU',
                phone: '+683'
            },
            {
                name: 'Tokelau',
                code: 'TK',
                phone: '+690'
            },
            {
                name: 'American Samoa',
                code: 'AS',
                phone: '+1-684'
            },
            {
                name: 'Guam',
                code: 'GU',
                phone: '+1-671'
            },
            {
                name: 'Northern Mariana Islands',
                code: 'MP',
                phone: '+1-670'
            },
            {
                name: 'Puerto Rico',
                code: 'PR',
                phone: '+1-787'
            },
            {
                name: 'U.S. Virgin Islands',
                code: 'VI',
                phone: '+1-340'
            },
            {
                name: 'Antigua and Barbuda',
                code: 'AG',
                phone: '+1-268'
            },
            {
                name: 'Bahamas',
                code: 'BS',
                phone: '+1-242'
            },
            {
                name: 'Barbados',
                code: 'BB',
                phone: '+1-246'
            },
            {
                name: 'Dominica',
                code: 'DM',
                phone: '+1-767'
            },
            {
                name: 'Dominican Republic',
                code: 'DO',
                phone: '+1-809'
            },
            {
                name: 'Grenada',
                code: 'GD',
                phone: '+1-473'
            },
            {
                name: 'Haiti',
                code: 'HT',
                phone: '+509'
            },
            {
                name: 'Jamaica',
                code: 'JM',
                phone: '+1-876'
            },
            {
                name: 'Saint Kitts and Nevis',
                code: 'KN',
                phone: '+1-869'
            },
            {
                name: 'Saint Lucia',
                code: 'LC',
                phone: '+1-758'
            },
            {
                name: 'Saint Vincent and the Grenadines',
                code: 'VC',
                phone: '+1-784'
            },
            {
                name: 'Trinidad and Tobago',
                code: 'TT',
                phone: '+1-868'
            },
            {
                name: 'Cuba',
                code: 'CU',
                phone: '+53'
            },
            {
                name: 'Aruba',
                code: 'AW',
                phone: '+297'
            },
            {
                name: 'Bonaire',
                code: 'BQ',
                phone: '+599'
            },
            {
                name: 'Curaçao',
                code: 'CW',
                phone: '+599'
            },
            {
                name: 'Sint Maarten',
                code: 'SX',
                phone: '+1-721'
            },
            {
                name: 'Cayman Islands',
                code: 'KY',
                phone: '+1-345'
            },
            {
                name: 'Bermuda',
                code: 'BM',
                phone: '+1-441'
            },
            {
                name: 'Greenland',
                code: 'GL',
                phone: '+299'
            },
            {
                name: 'Faroe Islands',
                code: 'FO',
                phone: '+298'
            },
            {
                name: 'Svalbard and Jan Mayen',
                code: 'SJ',
                phone: '+47'
            },
            {
                name: 'Åland Islands',
                code: 'AX',
                phone: '+358'
            },
            {
                name: 'Gibraltar',
                code: 'GI',
                phone: '+350'
            },
            {
                name: 'Guernsey',
                code: 'GG',
                phone: '+44-1481'
            },
            {
                name: 'Isle of Man',
                code: 'IM',
                phone: '+44-1624'
            },
            {
                name: 'Jersey',
                code: 'JE',
                phone: '+44-1534'
            },
            {
                name: 'Saint Helena',
                code: 'SH',
                phone: '+290'
            },
            {
                name: 'Ascension Island',
                code: 'AC',
                phone: '+247'
            },
            {
                name: 'Tristan da Cunha',
                code: 'TA',
                phone: '+290'
            },
            {
                name: 'South Sudan',
                code: 'SS',
                phone: '+211'
            },
            {
                name: 'Taiwan',
                code: 'TW',
                phone: '+886'
            },
            {
                name: 'Hong Kong',
                code: 'HK',
                phone: '+852'
            },
            {
                name: 'Macau',
                code: 'MO',
                phone: '+853'
            },
            {
                name: 'North Korea',
                code: 'KP',
                phone: '+850'
            }
        ];

        window.registrationForm = function() {
            return {
                currentStep: 1,
                isSubmitting: false,
                isSaving: false,
                countries: (window.countriesData || []).sort((a, b) => a.name.localeCompare(b.name)),
                formData: {
                    email: '',
                    password: '',
                    company_name: '',
                    phone: '',
                    country_code: '+91',
                    country: '',
                    company_type: 'national',
                    website: '',
                    description: '',
                    industry: '',
                    company_size: '',
                    address: {
                        street: '',
                        city: '',
                        state: '',
                        postal_code: '',
                        lat: null,
                        lng: null
                    },
                    tax_id: '',
                    documents: {
                        business_license: null,
                        tax_id: null,
                        address_proof: null,
                        director_id: null,
                        other: null
                    },
                    accept_terms: false
                },
                showPassword: false,
                passwordValid: false,
                passwordError: '',
                passwordStrengthText: '',
                passwordStrengthTextClass: '',
                passwordStrengthBarClass: 'bg-red-500',
                passwordStrengthBarStyle: 'width: 0%',
                passwordSuggestions: [],
                map: null,
                marker: null,
                init() {
                    this.$watch('formData.password', () => this.evaluatePassword());
                    this.$watch('currentStep', (step) => {
                        if (step === 2) {
                            this.$nextTick(() => {
                                this.initMap();
                                this.autoDetectLocation();
                            });
                        }
                    });
                },
                updatePhoneCodeFromCountry() {
                    if (this.formData.country) {
                        const selectedCountry = this.countries.find(c => c.name === this.formData.country);
                        if (selectedCountry) {
                            this.formData.country_code = selectedCountry.phone;
                        }
                    }
                },
                evaluatePassword() {
                    const pw = this.formData.password || '';
                    const checks = {
                        lowercase: /[a-z]/.test(pw),
                        uppercase: /[A-Z]/.test(pw),
                        number: /[0-9]/.test(pw),
                        special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(pw),
                        length: pw.length >= 8 && pw.length <= 20,
                        noCommon: !['password', 'password123', '12345678', 'qwerty', 'letmein', 'admin', 'welcome'].includes(pw.toLowerCase())
                    };
                    let score = 0;
                    if (checks.lowercase) score += 15;
                    if (checks.uppercase) score += 15;
                    if (checks.number) score += 15;
                    if (checks.special) score += 20;
                    if (checks.length) score += 20;
                    if (checks.noCommon) score += 15;
                    if (pw.length >= 12) score += 5;
                    if (pw.length >= 16) score += 5;
                    if (score < 30) {
                        this.passwordStrengthText = 'Very Weak';
                        this.passwordStrengthTextClass = 'text-red-600';
                        this.passwordStrengthBarClass = 'bg-red-500';
                        this.passwordStrengthBarStyle = 'width: 20%';
                    } else if (score < 50) {
                        this.passwordStrengthText = 'Weak';
                        this.passwordStrengthTextClass = 'text-orange-600';
                        this.passwordStrengthBarClass = 'bg-orange-500';
                        this.passwordStrengthBarStyle = 'width: 40%';
                    } else if (score < 70) {
                        this.passwordStrengthText = 'Fair';
                        this.passwordStrengthTextClass = 'text-yellow-600';
                        this.passwordStrengthBarClass = 'bg-yellow-500';
                        this.passwordStrengthBarStyle = 'width: 60%';
                    } else if (score < 90) {
                        this.passwordStrengthText = 'Good';
                        this.passwordStrengthTextClass = 'text-green-600';
                        this.passwordStrengthBarClass = 'bg-green-500';
                        this.passwordStrengthBarStyle = 'width: 80%';
                    } else {
                        this.passwordStrengthText = 'Strong';
                        this.passwordStrengthTextClass = 'text-green-700';
                        this.passwordStrengthBarClass = 'bg-green-600';
                        this.passwordStrengthBarStyle = 'width: 100%';
                    }
                    this.passwordValid = Object.values(checks).every(Boolean);
                    this.passwordError = this.passwordValid || pw.length === 0 ? '' : 'Password does not meet all requirements';
                    this.passwordSuggestions = [];
                    if (!checks.lowercase) this.passwordSuggestions.push('Add lowercase letters (a-z)');
                    if (!checks.uppercase) this.passwordSuggestions.push('Add uppercase letters (A-Z)');
                    if (!checks.number) this.passwordSuggestions.push('Add numbers (0-9)');
                    if (!checks.special) this.passwordSuggestions.push('Add special characters (!@#$%^&*)');
                    if (!checks.length) this.passwordSuggestions.push('Use 8–20 characters');
                    if (!checks.noCommon) this.passwordSuggestions.push('Avoid common passwords');
                },
                handleFileUpload(event, type) {
                    const file = event.target.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File size must be less than 2MB');
                            event.target.value = '';
                            return;
                        }
                        const fileData = {
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            file: file,
                            preview: null
                        };
                        
                        // Create preview for images and PDFs
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                fileData.preview = e.target.result;
                                this.formData.documents[type] = fileData;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            // For PDFs, create object URL for iframe preview
                            fileData.preview = null;
                            fileData.previewURL = URL.createObjectURL(file);
                            this.formData.documents[type] = fileData;
                        }
                    } else {
                        this.formData.documents[type] = null;
                    }
                },
                dragOver(event, type) {
                    event.currentTarget.classList.add('active');
                    event.currentTarget.classList.add('accept');
                },
                dragLeave(event, type) {
                    event.currentTarget.classList.remove('active');
                    event.currentTarget.classList.remove('accept');
                },
                dropFile(event, type) {
                    event.currentTarget.classList.remove('active');
                    event.currentTarget.classList.remove('accept');
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        if (file.size > 2 * 1024 * 1024) {
                            alert('File size must be less than 2MB');
                            return;
                        }
                        const fileData = {
                            name: file.name,
                            size: file.size,
                            type: file.type,
                            file: file,
                            preview: null
                        };
                        
                        // Create preview for images and PDFs
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                fileData.preview = e.target.result;
                                this.formData.documents[type] = fileData;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            // For PDFs, create object URL for iframe preview
                            fileData.preview = null;
                            fileData.previewURL = URL.createObjectURL(file);
                            this.formData.documents[type] = fileData;
                        }
                    }
                },
                openPreview(type) {
                    const doc = this.formData.documents[type];
                    if (!doc || !doc.file) return;
                    
                    // Ensure previewURL exists
                    if (!doc.previewURL && !doc.preview) {
                        if (doc.file.type.startsWith('image/')) {
                            // For images, create data URL
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                doc.preview = e.target.result;
                            };
                            reader.readAsDataURL(doc.file);
                        } else {
                            // For PDFs, create object URL
                            doc.previewURL = URL.createObjectURL(doc.file);
                        }
                    }
                },
                removeDocument(type) {
                    const doc = this.formData.documents[type];
                    if (doc && doc.previewURL) {
                        URL.revokeObjectURL(doc.previewURL);
                    }
                    this.formData.documents[type] = null;
                },
                async saveStep(step) {
                    this.isSaving = true;
                    try {
                        // Save form data to localStorage
                        const savedData = {
                            step: step,
                            formData: JSON.parse(JSON.stringify(this.formData)),
                            timestamp: new Date().toISOString()
                        };
                        
                        // Remove file objects before saving to localStorage (they can't be serialized)
                        if (savedData.formData.documents) {
                            Object.keys(savedData.formData.documents).forEach(key => {
                                if (savedData.formData.documents[key] && savedData.formData.documents[key].file) {
                                    savedData.formData.documents[key] = {
                                        name: savedData.formData.documents[key].name,
                                        size: savedData.formData.documents[key].size,
                                        type: savedData.formData.documents[key].type
                                    };
                                }
                            });
                        }
                        
                        localStorage.setItem('employer_registration_draft', JSON.stringify(savedData));
                        
                        // Show success message
                        alert('Progress saved successfully! You can continue later.');
                        this.isSaving = false;
                    } catch (error) {
                        console.error('Error saving step:', error);
                        alert('Failed to save progress. Please try again.');
                        this.isSaving = false;
                    }
                },
                loadSavedData() {
                    try {
                        const saved = localStorage.getItem('employer_registration_draft');
                        if (saved) {
                            const savedData = JSON.parse(saved);
                            if (savedData.formData && confirm('Found saved progress. Would you like to continue from where you left off?')) {
                                // Merge saved data with current form data
                                Object.keys(savedData.formData).forEach(key => {
                                    if (key !== 'documents') {
                                        this.formData[key] = savedData.formData[key] || this.formData[key];
                                    }
                                });
                                this.currentStep = savedData.step || 1;
                            }
                        }
                    } catch (error) {
                        console.error('Error loading saved data:', error);
                    }
                },
                initMap() {
                    if (this.map) return;
                    this.map = L.map('employer-map').setView([20.0, 0.0], 2);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(this.map);
                    this.marker = L.marker([20.0, 0.0], {
                        draggable: true
                    }).addTo(this.map);
                    this.marker.on('dragend', () => {
                        const pos = this.marker.getLatLng();
                        this.formData.address.lat = pos.lat;
                        this.formData.address.lng = pos.lng;
                        this.reverseGeocode(pos.lat, pos.lng);
                    });
                },
                autoDetectLocation() {
                    if (!confirm('We want to know your location to ensure employer authenticity. Allow location access?')) {
                        return;
                    }
                    if (!navigator.geolocation) return;
                    navigator.geolocation.getCurrentPosition((position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        this.formData.address.lat = lat;
                        this.formData.address.lng = lng;
                        if (this.map && this.marker) {
                            this.map.setView([lat, lng], 15);
                            this.marker.setLatLng([lat, lng]);
                        }
                        this.reverseGeocode(lat, lng);
                    }, () => {}, {
                        enableHighAccuracy: true,
                        timeout: 8000
                    });
                },
                reverseGeocode(lat, lng) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                        .then(res => res.json())
                        .then(data => {
                            const addr = data.address || {};
                            this.formData.address.street = addr.road || this.formData.address.street;
                            this.formData.address.city = addr.city || addr.town || addr.village || this.formData.address.city;
                            this.formData.address.state = addr.state || this.formData.address.state;
                            this.formData.address.postal_code = addr.postcode || this.formData.address.postal_code;
                            const countryName = addr.country || '';
                            if (countryName) {
                                this.formData.country = countryName;
                                this.updatePhoneCodeFromCountry();
                            }
                        })
                        .catch(() => {});
                },
                updateCountryType() {
                    if (this.formData.country === 'India') {
                        this.formData.company_type = 'national';
                    } else {
                        this.formData.company_type = 'international';
                    }
                },
                async submitRegistration() {
                    this.isSubmitting = true;
                    try {
                        if (!this.formData.accept_terms) {
                            alert('Please accept the terms and conditions');
                            this.isSubmitting = false;
                            return;
                        }
                        if (!this.formData.address.lat || !this.formData.address.lng) {
                            alert('Please pin your exact location on the map');
                            this.isSubmitting = false;
                            return;
                        }
                        if (!this.formData.documents.business_license || !this.formData.documents.business_license.file ||
                            !this.formData.documents.tax_id || !this.formData.documents.tax_id.file ||
                            !this.formData.documents.address_proof || !this.formData.documents.address_proof.file) {
                            alert('Please upload all required documents');
                            this.isSubmitting = false;
                            return;
                        }
                        if (this.formData.company_type === 'international' &&
                            (!this.formData.documents.director_id || !this.formData.documents.director_id.file)) {
                            alert('Director ID is required for international companies');
                            this.isSubmitting = false;
                            return;
                        }
                        const formData = new FormData();
                        formData.append('email', this.formData.email);
                        formData.append('password', this.formData.password);
                        formData.append('role', 'employer');
                        formData.append('company_name', this.formData.company_name);
                        formData.append('phone', this.formData.phone);
                        formData.append('country_code', this.formData.country_code);
                        formData.append('country', this.formData.country);
                        formData.append('company_type', this.formData.company_type);
                        formData.append('website', this.formData.website);
                        formData.append('description', this.formData.description);
                        formData.append('industry', this.formData.industry);
                        formData.append('company_size', this.formData.company_size);
                        formData.append('address', JSON.stringify(this.formData.address));
                        formData.append('tax_id', this.formData.tax_id);
                        const csrfToken = this.getCsrfToken();
                        if (!csrfToken) {
                            alert('CSRF token not found. Please refresh the page and try again.');
                            this.isSubmitting = false;
                            return;
                        }
                        formData.append('_token', csrfToken);
                        if (this.formData.documents.business_license && this.formData.documents.business_license.file) {
                            formData.append('doc_business_license', this.formData.documents.business_license.file);
                        }
                        if (this.formData.documents.tax_id && this.formData.documents.tax_id.file) {
                            formData.append('doc_tax_id', this.formData.documents.tax_id.file);
                        }
                        if (this.formData.documents.address_proof && this.formData.documents.address_proof.file) {
                            formData.append('doc_address_proof', this.formData.documents.address_proof.file);
                        }
                        if (this.formData.documents.director_id && this.formData.documents.director_id.file) {
                            formData.append('doc_director_id', this.formData.documents.director_id.file);
                        }
                        if (this.formData.documents.other && this.formData.documents.other.file) {
                            formData.append('doc_other', this.formData.documents.other.file);
                        }
                        const response = await fetch('/register-employer', {
                            method: 'POST',
                            body: formData
                        });
                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            console.error('Failed to parse response:', e);
                            alert('Registration failed: Invalid server response. Check console for details.');
                            this.isSubmitting = false;
                            return;
                        }
                        if (response.ok && data.success) {
                            const redirectUrl = data.redirect || '/employer/dashboard';
                            console.log('✓ Registration successful! Redirecting to:', redirectUrl);
                            window.location.href = redirectUrl;
                        } else {
                            const errorMsg = data.error || data.message || data.errors || 'Registration failed';
                            console.error('Registration error:', data);
                            alert('Error: ' + (typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg));
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                getCsrfToken() {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (!meta) {
                        console.error('CSRF token meta tag not found');
                        return '';
                    }
                    const token = meta.getAttribute('content');
                    if (!token) {
                        console.error('CSRF token is empty');
                    }
                    return token || '';
                }
            }
        }
    </script>
</body>
</html>
