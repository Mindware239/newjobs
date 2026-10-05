<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Controllers\Front\SkillDevelopmentController;
use App\Helpers\DataCipher;

/**
 * Validates a submission against its FormRegistry definition.
 * Returns [columns, details, errors]; errors are [hi, en] pairs keyed by field.
 */
class RegistrationValidator
{
    public const VIDEO_MAX_BYTES = 200 * 1024 * 1024;

    /** @param array<string, array|null> $files uploaded files keyed by field (resume, video) */
    public static function validate(array $form, array $in, array $files = []): array
    {
        $cols = [];
        $details = [];
        $errors = [];

        // International forms (jobs abroad, hiring companies): residents of Nepal, Sri Lanka,
        // Afghanistan, Bangladesh, China and Thailand give a mobile with country code, their country
        // instead of an Indian state, and their local postal code.
        $residence = 'India';
        if (!empty($form['international']) && is_string($in['residence_country'] ?? null) && isset(FormRegistry::OPEN_COUNTRIES[$in['residence_country']])) {
            $residence = $in['residence_country'];
        }
        $foreign = $residence !== 'India';

        foreach (FormRegistry::fields($form) as $key => $f) {
            $required = (bool)$f['required'];
            $raw = $in[$key] ?? null;
            $value = null;

            switch ($f['type']) {
                case 'text':
                case 'textarea':
                    $value = self::clean($raw, (int)($f['max'] ?? ($f['type'] === 'textarea' ? 2000 : 190)));
                    if ($required && mb_strlen($value) < 2) {
                        $errors[$key] = ['यह जानकारी भरें', 'This field is required'];
                    }
                    break;

                case 'email':
                    $value = self::clean($raw, 190);
                    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$key] = ['सही ईमेल भरें', 'Enter a valid email'];
                    } elseif ($required && $value === '') {
                        $errors[$key] = ['ईमेल भरें', 'Email is required'];
                    }
                    break;

                case 'mobile':
                    $s = trim((string)$raw);
                    $value = $s === '' ? '' : (($foreign ? self::intlMobile($s, $residence) : self::mobile($s)) ?? '');
                    if ($s !== '' && $value === '') {
                        $errors[$key] = ['सही 10 अंकों का मोबाइल नंबर भरें', 'Enter a valid 10-digit mobile number'];
                    } elseif ($required && $value === '') {
                        $errors[$key] = ['मोबाइल नंबर भरें', 'Mobile number is required'];
                    }
                    break;

                case 'aadhaar':
                    // Only the last 4 digits are stored (UIDAI rules restrict storing full Aadhaar numbers).
                    $digits = preg_replace('/\D/', '', (string)$raw);
                    if ($digits !== '' && !preg_match('/^[2-9]\d{11}$/', $digits)) {
                        $errors[$key] = ['सही 12 अंकों का आधार नंबर भरें', 'Enter a valid 12-digit Aadhaar number'];
                    } elseif ($required && $digits === '') {
                        $errors[$key] = ['आधार नंबर भरें', 'Aadhaar number is required'];
                    }
                    $cols['aadhaar_last4'] = $digits !== '' ? substr($digits, -4) : null;
                    continue 2;

                case 'date':
                    $t = $raw ? strtotime((string)$raw) : false;
                    if (($f['date_mode'] ?? '') === 'expiry') {
                        // Passport / document expiry: still valid today, at most 11 years ahead.
                        if ($t && $t > strtotime('today') && $t <= strtotime('+11 years')) {
                            $value = date('Y-m-d', $t);
                        } elseif ($required || $raw) {
                            $errors[$key] = ['वैध (समाप्त न हुई) तारीख भरें', 'Enter a valid, not-expired date'];
                        }
                        break;
                    }
                    if (($f['date_mode'] ?? '') === 'future') {
                        // Availability dates: today up to one year ahead.
                        if ($t && $t >= strtotime('today') && $t <= strtotime('+1 year')) {
                            $value = date('Y-m-d', $t);
                        } elseif ($required || $raw) {
                            $errors[$key] = ['आज से 1 साल के भीतर की तारीख भरें', 'Enter a date between today and one year ahead'];
                        }
                        break;
                    }
                    if ($t && $t <= strtotime('-14 years') && $t >= strtotime('-100 years')) {
                        $value = date('Y-m-d', $t);
                    } elseif ($required || $raw) {
                        $errors[$key] = ['सही जन्मतिथि भरें (कम से कम 14 वर्ष)', 'Enter a valid date of birth (minimum age 14)'];
                    }
                    break;

                case 'number':
                    $s = trim((string)$raw);
                    if ($s === '') {
                        if ($required) {
                            $errors[$key] = ['यह जानकारी भरें', 'This field is required'];
                        }
                    } elseif (!ctype_digit($s) || (int)$s < ($f['min'] ?? 0) || (int)$s > ($f['maxv'] ?? PHP_INT_MAX)) {
                        $errors[$key] = isset($f['min'], $f['maxv']) && $f['maxv'] >= 1000
                            ? ['₹' . self::inr((int)$f['min']) . ' से ₹' . self::inr((int)$f['maxv']) . ' के बीच राशि भरें', 'Enter an amount between ₹' . self::inr((int)$f['min']) . ' and ₹' . self::inr((int)$f['maxv'])]
                            : ['सही संख्या भरें', 'Enter a valid number'];
                    } else {
                        $value = (int)$s;
                    }
                    break;

                case 'geo':
                    // "lat,lng" from the browser's location button (optional; must be inside India's bounding box).
                    $value = null;
                    if (is_string($raw) && preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', $raw, $g)
                        && (float)$g[1] >= 6 && (float)$g[1] <= 37.5 && (float)$g[2] >= 68 && (float)$g[2] <= 98) {
                        $value = round((float)$g[1], 5) . ',' . round((float)$g[2], 5);
                    }
                    break;

                case 'pincode':
                    if ($foreign) {
                        $pc = preg_replace('/\D/', '', (string)$raw);
                        $value = null;
                        if ($pc !== '' && preg_match('/^\d{3,6}$/', $pc)) {
                            $details['postal_code'] = $pc; // foreign postal codes are not Indian PINs
                        }
                        break;
                    }
                    $value = preg_replace('/\D/', '', (string)$raw);
                    if (!preg_match('/^[1-9]\d{5}$/', $value)) {
                        $errors[$key] = ['सही 6 अंकों का पिन कोड भरें', 'Enter a valid 6-digit PIN code'];
                    }
                    break;

                case 'state':
                    $value = (string)$raw;
                    if ($foreign) {
                        $value = self::clean($raw, 80) ?: $residence;
                        break;
                    }
                    if (!in_array($value, array_column(SkillDevelopmentController::STATES, 0), true)) {
                        $errors[$key] = ['राज्य चुनें', 'Select State / UT'];
                    }
                    break;

                case 'radio':
                case 'select':
                    $value = (string)$raw;
                    if (!isset($f['options'][$value])) {
                        $value = null;
                        if ($required) {
                            $errors[$key] = ['एक विकल्प चुनें', 'Select an option'];
                        }
                    }
                    break;

                case 'checkboxes':
                    $value = array_values(array_intersect((array)$raw, array_map('strval', array_keys($f['options']))));
                    if ($required && !$value) {
                        $errors[$key] = ['कम से कम एक विकल्प चुनें', 'Select at least one option'];
                    }
                    break;

                case 'categories':
                    $picked = [];
                    foreach ((array)$raw as $c) {
                        $c = self::clean($c, 80);
                        if ($c !== '' && !in_array(mb_strtolower($c), array_map('mb_strtolower', $picked), true)) {
                            $picked[] = $c;
                        }
                    }
                    $picked = array_slice($picked, 0, FormRegistry::maxCategories($form));
                    $minPick = FormRegistry::minCategories($form);
                    if ($required && !$picked) {
                        $errors[$key] = $minPick > 1
                            ? ["कम से कम {$minPick} विकल्प चुनें", "Select at least {$minPick} options"]
                            : ['सूची से कम से कम एक चुनें या लिखें', 'Select or type at least one'];
                    } elseif ($picked && count($picked) < $minPick) {
                        $errors[$key] = ["कम से कम {$minPick} विकल्प चुनें (अधिकतम " . FormRegistry::maxCategories($form) . ')', "Select at least {$minPick} options (maximum " . FormRegistry::maxCategories($form) . ')'];
                    }
                    $value = implode(', ', $picked);
                    break;

                case 'file':
                    $file = $files[$key] ?? null;
                    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        $problem = self::checkResume($file, !empty($f['allow_images']));
                        if ($problem) {
                            $errors[$key] = $problem;
                        }
                    }
                    continue 2;

                case 'image':
                case 'selfie':
                    $file = $files[$key] ?? null;
                    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        $problem = self::checkImage($file);
                        if ($problem) {
                            $errors[$key] = $problem;
                        }
                    } elseif ($required) {
                        $errors[$key] = $f['type'] === 'selfie'
                            ? ['लाइव सेल्फ़ी लेना ज़रूरी है', 'Please take a live selfie']
                            : ['फोटो अपलोड करना ज़रूरी है', 'Please upload a photo'];
                    }
                    continue 2;

                case 'video':
                    $file = $files[$key] ?? null;
                    $hasFile = $file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
                    $link = self::clean($in['video_link'] ?? '', 500);
                    if ($hasFile) {
                        $problem = self::checkVideo($file);
                        if ($problem) {
                            $errors[$key] = $problem;
                        }
                    } elseif ($link !== '' && !preg_match('#^https?://\S+\.\S+#i', $link)) {
                        $errors[$key] = ['सही वीडियो लिंक दें (https://…)', 'Enter a valid video link (https://…)'];
                    } elseif ($required && $link === '') {
                        $errors[$key] = ['डेमो वीडियो अपलोड करें या लिंक दें', 'Upload a demo video or give a video link'];
                    }
                    continue 2;

                case 'gst':
                    $gst = ProviderIdentity::normalizeGst(is_scalar($raw) ? (string)$raw : '');
                    if (!empty($in['no_gst'])) {
                        $details['no_gst'] = 1;
                        continue 2;
                    }
                    if ($gst === '') {
                        if ($required) {
                            $errors[$key] = ['GST नंबर भरें या “मेरे पास GST नहीं है” चुनें', 'Enter GST number or tick “I don’t have GST”'];
                        }
                    } elseif (!preg_match(ProviderIdentity::GST_PATTERN, $gst)) {
                        $errors[$key] = ['सही 15 अंकों का GST नंबर भरें', 'Enter a valid 15-character GST number'];
                    } else {
                        $value = $gst;
                    }
                    break;

                case 'passport':
                    $pp = strtoupper(preg_replace('/[\s-]+/', '', is_scalar($raw) ? (string)$raw : ''));
                    if ($pp === '') {
                        if ($required) {
                            $errors[$key] = ['पासपोर्ट नंबर भरें', 'Enter your passport number'];
                        }
                    } elseif (!preg_match('/^[A-Z][0-9]{7}$|^[A-Z0-9]{6,9}$/', $pp)) {
                        $errors[$key] = ['सही पासपोर्ट नंबर भरें (जैसे K1234567)', 'Enter a valid passport number (e.g. K1234567)'];
                    } else {
                        // Stored encrypted; only the last 3 characters are kept readable.
                        $details['passport_enc'] = DataCipher::encrypt($pp);
                        $details['passport_last3'] = substr($pp, -3);
                    }
                    continue 2;

                case 'bank_account':
                    $acct = preg_replace('/\s+/', '', is_scalar($raw) ? (string)$raw : '');
                    if ($acct === '') {
                        if ($required) {
                            $errors[$key] = ['बैंक खाता संख्या भरें', 'Enter bank account number'];
                        }
                    } elseif (!preg_match('/^\d{9,18}$/', $acct)) {
                        $errors[$key] = ['सही खाता संख्या भरें (9–18 अंक)', 'Enter a valid account number (9–18 digits)'];
                    } else {
                        $details['bank_account_enc'] = DataCipher::encrypt($acct);
                        $details['bank_account_last4'] = substr($acct, -4);
                    }
                    continue 2;

                case 'ifsc':
                    $value = strtoupper(preg_replace('/\s+/', '', is_scalar($raw) ? (string)$raw : ''));
                    if ($value === '') {
                        if ($required) {
                            $errors[$key] = ['IFSC कोड भरें', 'Enter IFSC code'];
                        }
                    } elseif (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $value)) {
                        $errors[$key] = ['सही IFSC कोड भरें (जैसे SBIN0001234)', 'Enter a valid IFSC code (e.g. SBIN0001234)'];
                        $value = null;
                    }
                    break;
            }

            if (!empty($f['other'])) {
                $other = self::clean($in[$f['other']] ?? '', 190);
                if ($other !== '') {
                    $details[$f['other']] = $other;
                }
            }

            if ($value === null || $value === '' || $value === []) {
                continue;
            }
            if (in_array($key, FormRegistry::COLUMN_FIELDS, true)) {
                $cols[$key] = $value;
            } else {
                $details[$key] = $value;
            }
        }

        // Internship: a paid internship needs a stipend inside the allowed range; unpaid ignores it.
        if (($form['type'] ?? '') === 'internship') {
            $pay = (string)($details['internship_pay'] ?? '');
            if ($pay === 'unpaid') {
                unset($details['stipend_expectation']);
            } elseif ($pay === 'paid' && empty($details['stipend_expectation']) && !isset($errors['stipend_expectation'])) {
                $errors['stipend_expectation'] = ['पेड इंटर्नशिप के लिए स्टाइपेंड (₹8,000 – ₹5,00,000) भरें', 'Enter the expected stipend for a paid internship (₹8,000 – ₹5,00,000)'];
            }
        }

        if (empty($in['declaration'])) {
            $errors['declaration'] = ['आगे बढ़ने के लिए सभी नियम व शर्तें स्वीकार करें', 'Please accept all terms & conditions'];
        }

        return [$cols, $details, $errors];
    }

    /** Returns [hi, en] error or null. */
    public static function checkResume(array $file, bool $allowImages = false): ?array
    {
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
            return ['फ़ाइल अपलोड नहीं हो सकी', 'File could not be uploaded'];
        }
        if ((int)$file['size'] > 5 * 1024 * 1024) {
            return ['फ़ाइल 5MB से बड़ी है', 'File is larger than 5MB'];
        }
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
        $allowed = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        ];
        if ($allowImages) {
            $allowed += ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png']];
        }
        if (!isset($allowed[$ext]) || !in_array($mime, $allowed[$ext], true)) {
            return $allowImages ? ['केवल PDF, DOC, DOCX, JPG या PNG फ़ाइल', 'Only PDF, DOC, DOCX, JPG or PNG files'] : ['केवल PDF, DOC या DOCX फ़ाइल', 'Only PDF, DOC or DOCX files'];
        }
        return null;
    }

    /** JPG / PNG / WEBP photo up to 5MB that really decodes as an image. Returns [hi, en] error or null. */
    public static function checkImage(array $file): ?array
    {
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
            return ['फोटो अपलोड नहीं हो सकी', 'Photo could not be uploaded'];
        }
        if ((int)$file['size'] > 5 * 1024 * 1024) {
            return ['फोटो 5MB से बड़ी है', 'Photo is larger than 5MB'];
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
        $info = @getimagesize((string)$file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || !$info || $info[0] < 100 || $info[1] < 100) {
            return ['केवल साफ़ JPG, PNG या WEBP फोटो (कम से कम 100×100)', 'Only a clear JPG, PNG or WEBP photo (at least 100×100)'];
        }
        return null;
    }

    /** Returns [hi, en] error or null. */
    public static function checkVideo(array $file): ?array
    {
        $err = (int)($file['error'] ?? UPLOAD_ERR_OK);
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE || (int)($file['size'] ?? 0) > self::VIDEO_MAX_BYTES) {
            return ['वीडियो 200MB से बड़ा है – कृपया लिंक दें', 'Video is larger than 200MB – please give a link instead'];
        }
        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
            return ['वीडियो अपलोड नहीं हो सका – दोबारा प्रयास करें या लिंक दें', 'Video could not be uploaded – retry or give a link'];
        }
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $mime = (string)(new \finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']);
        if (!in_array($ext, ['mp4', 'mov', 'webm', 'mkv', '3gp', 'm4v'], true) || !str_starts_with($mime, 'video/')) {
            return ['केवल वीडियो फ़ाइल (MP4, MOV, WEBM, MKV, 3GP)', 'Only video files (MP4, MOV, WEBM, MKV, 3GP)'];
        }
        return null;
    }

    /** Indian digit grouping: 500000 → 5,00,000 */
    public static function inr(int $n): string
    {
        $s = (string)abs($n);
        if (strlen($s) > 3) {
            $last3 = substr($s, -3);
            $rest = substr($s, 0, -3);
            $s = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last3;
        }
        return ($n < 0 ? '-' : '') . $s;
    }

    /** Mobile of a resident of $country (open countries): "+977…" (country code + 6–12 digits). */
    public static function intlMobile(string $raw, string $country): ?string
    {
        $code = FormRegistry::OPEN_COUNTRIES[$country] ?? null;
        if ($code === null) {
            return null;
        }
        $d = preg_replace('/\D/', '', $raw);
        if (str_starts_with($d, '00' . $code)) {
            $d = substr($d, 2 + strlen($code));
        } elseif (str_starts_with($d, $code) && strlen($d) > strlen($code) + 6) {
            $d = substr($d, strlen($code));
        }
        $d = ltrim($d, '0');
        return preg_match('/^\d{6,12}$/', $d) ? '+' . $code . $d : null;
    }

    public static function mobile(string $raw): ?string
    {
        $d = preg_replace('/\D/', '', $raw);
        if (strlen($d) === 12 && str_starts_with($d, '91')) {
            $d = substr($d, 2);
        } elseif (strlen($d) === 11 && $d[0] === '0') {
            $d = substr($d, 1);
        }
        return preg_match('/^[6-9]\d{9}$/', $d) ? $d : null;
    }

    private static function clean($v, int $max): string
    {
        return mb_substr(trim(strip_tags(is_scalar($v) ? (string)$v : '')), 0, $max);
    }
}
