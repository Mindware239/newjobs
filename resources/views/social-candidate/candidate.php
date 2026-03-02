<?php $scheme = $_SERVER['REQUEST_SCHEME'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = isset($base) && is_string($base) ? $base : ($scheme . '://' . $host); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mindware Infotech - Candidate</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
</head>

<body class="bg-white">
<div class="border-b">
    <div class="max-w-6xl mx-auto flex items-center justify-between h-22">
        <img
                src="<?php echo $base; ?>/uploads/Mindware-infotech.png"
                alt="Mindware Infotech Logo"
                class="h-12 object-contain mr-auto">

        <nav class="hidden min-[900px]:flex items-center gap-4 lg:gap-8 text-base font-medium">
            <a href="/candidatelisting" class="hover:text-custom-red transition-colors pb-1">Applications & saved
                listings</a>
            <a href="/social-candidate/candidatesubscriptions" class="hover:text-custom-red transition-colors pb-1">Job
                alerts</a>
            <a href="/social-candidate/accountcandidate" class="text-custom-red border-b-2 border-custom-red pb-1">Account
                & profile</a>
            <a href="/social-services/logout" class="text-black hover:text-custom-red transition-colors pb-1">Logout</a>
        </nav>
    </div>
    <div class="bg-black text-white text-sm">

        <div class="max-w-6xl mx-auto h-12 flex items-center justify-center gap-20 font-semibold">
        </div>
        <div class="max-w-6xl mx-auto h-12 flex items-center justify-center gap-20 font-semibold">
            <a href="<?= rtrim($base, '/') ?>/social-services" class="hover:text-[#5b6bd5]">
                ← Back to Home
            </a>

            <li><a href="<?= rtrim($base, '/') ?>/find-a-job" class="hover:text-[#5b6bd5]">Find a job</a></li>
            <span class="cursor-pointer">Search employers</span>
            <span class="cursor-pointer">Career insights</span>
            <li><a href="<?= rtrim($base, '/') ?>/about" class="hover:text-[#5b6bd5]">About us</a></li>
            <li><a href="<?= rtrim($base, '/') ?>/help" class="hover:text-[#5b6bd5]">Get Help</a></li>

        </div>

    </div>

</div>

</div>

<div class="max-w-6xl mx-auto mt-10">

    <!-- ================= PROGRESS ================= -->

    <div class="flex justify-center space-x-12 mb-10 text-sm">

        <div class="flex items-center space-x-2">
            <div id="p1" class="w-8 h-8 bg-blue-800 text-white rounded-full flex justify-center items-center">1</div>
            <span>Applications</span>
        </div>

        <div class="flex items-center space-x-2">
            <div id="p2" class="w-8 h-8 bg-gray-300 rounded-full flex justify-center items-center">2</div>
            <span>Application</span>
        </div>

        <div class="flex items-center space-x-2">
            <div id="p3" class="w-8 h-8 bg-gray-300 rounded-full flex justify-center items-center">3</div>
            <span>Confirmation</span>
        </div>

    </div>
    <div id="step1">

        <div class="bg-gray-200 p-4 rounded grid grid-cols-2 gap-4 mb-6">

            <div>
                <p class="text-sm font-semibold mb-1">Search by keyword</p>
                <input class="w-full border bg-white p-2 rounded" placeholder="Enter a keyword">
            </div>

            <div>
                <p class="text-sm font-semibold mb-1">Filter by organization</p>
                <select class="w-full border bg-white p-2 rounded text-gray-600">
                    <option>Select an organization</option>
                    <option>Global Servant Leaders Inc.</option>
                    <option>Soccer in the Streets</option>
                    <option>Hope Foundation</option>
                </select>
            </div>

        </div>

        <div class="flex justify-between items-center mb-4 border-b pb-2">

            <div class="flex space-x-3">
                <button class="bg-blue-900 text-white px-6 py-2 rounded text-sm">All</button>
                <button class="px-4 py-2 text-sm text-gray-700">Applied To</button>
                <button class="px-4 py-2 text-sm text-gray-700">Not Yet Applied To</button>
            </div>

            <div class="flex items-center space-x-4 text-sm">
                <p>2 results of 2 total</p>
                <span class="bg-red-500 text-white px-2 rounded">1</span>
                <button class="text-red-500">Refresh</button>
            </div>

        </div>

        <div class="bg-gray-200 p-6 rounded flex justify-between mb-6">

            <div class="max-w-3xl">

                <h2 class="font-semibold mb-1">Grant Writer</h2>
                <p class="text-sm mb-3">Hillside Food Outreach</p>

                <p class="text-sm text-gray-700 mb-3">
                    We are seeking a reliable and detail-oriented grant writer to support our nonprofit programs.
                </p>

                <span class="text-red-500 text-sm">Open listing</span>

            </div>

            <div class="text-right">

                <button onclick="goStep2()" class="bg-red-500 text-white px-6 py-2 rounded text-sm mb-3">
                    Re-submit / edit application
                </button>

                <p class="text-sm font-semibold">Status:</p>
                <p class="text-sm">SUBMITTED</p>

            </div>

        </div>

    </div>

    <!-- ================= STEP 2 ================= -->
    <!-- ================= STEP 2 ================= -->

    <form id="step2"
          class="hidden"
          action="/candidate/submit-application"
          method="POST"
          enctype="multipart/form-data">
        <input type="hidden" name="job_id" value="<?= (int)($_GET['job_id'] ?? 0) ?>">

        <p class="text-sm text-red-500 mb-4 cursor-pointer" onclick="backStart()">
            ◀ Back to your saved listings
        </p>

        <h3 class="font-semibold mb-6">
            Your Application for: Grant Writer with Hillside Food Outreach
        </h3>

        <!-- ================= EMAIL ================= -->

        <div class="bg-gray-200 p-4 rounded mb-4">

            <p class="text-sm font-semibold mb-1">Notification email address *</p>

            <input name="email"
                   class="w-full border bg-white p-2 rounded"
                   value="sales@indianbarcode.com">

        </div>

        <!-- ================= FILE UPLOAD ================= -->

        <div class="bg-gray-200 p-4 rounded mb-4">

            <p class="text-sm font-semibold mb-2">Attach files & documents</p>

            <div class="grid grid-cols-3 gap-3">

                <input type="file"
                       name="resume"
                       id="fileInput"
                       class="border bg-white p-2 rounded text-sm"
                       onchange="fillFileName()">

                <input id="fileNameField"
                       class="border bg-white p-2 rounded text-sm"
                       placeholder="File name" readonly>

                <button type="button"
                        id="uploadBtn"
                        class="bg-red-300 text-white px-4 py-2 rounded text-sm cursor-not-allowed"
                        disabled>
                    Upload
                </button>

            </div>

        </div>

        <!-- ================= MESSAGE ================= -->

        <div class="bg-gray-200 p-4 rounded mb-6">

            <p class="text-sm mb-2">
                <span class="font-semibold">Optional:</span>
                Use the text box below to leave a note for the employer or manually draft your resume / cover letter.
            </p>

            <!-- TOOLBAR (UI only) -->

            <div class="bg-white border border-gray-300 rounded-t px-2 py-1 flex flex-wrap items-center gap-2 text-sm">

                <select class="border px-2 py-1 rounded text-sm">
                    <option>Normal</option>
                    <option>Heading</option>
                </select>

                <button class="font-bold">B</button>
                <button class="italic">I</button>
                <button class="underline">U</button>
                <button>S</button>
                <button>❝</button>
                <button>❞</button>

                <button>⇦</button>
                <button>⇨</button>

                <button>≡</button>
                <button>≣</button>
                <button>≢</button>

                <button>🔗</button>
                <button>🖼</button>

                <button>Tx</button>

            </div>

            <!-- TEXTAREA -->

            <textarea name="message"
                      rows="6"
                      class="w-full border border-t-0 bg-white p-3 rounded-b resize-none focus:outline-none">
t4rteetttw
</textarea>

        </div>

        <!-- ================= SUBMIT ================= -->

        <div class="flex justify-end">

            <!-- <button id="resubmitBtn"
             type="submit"
             class="bg-red-300 text-white px-16 py-2 rounded cursor-not-allowed"
             disabled>
            ⟳ Re-submit
            </button> -->
            <button id="resubmitBtn"
                    type="button"
                    onclick="submitForm()"
                    class="bg-red-300 text-white px-16 py-2 rounded cursor-not-allowed"
                    disabled>
                ⟳ Re-submit
            </button>


        </div>

    </form>


    <!-- ================= STEP 3 ================= -->

    <div id="step3" class="hidden text-center mt-10">

        <p class="text-sm text-red-500 mb-4 cursor-pointer" onclick="backStart()">
            ◀ Back to your saved listings
        </p>

        <h3 class="font-semibold mb-8">
            Your Application for: Grant Writer with Hillside Food Outreach
        </h3>

        <div class="flex justify-center items-center mb-8">

            <div class="flex flex-col items-center">
                <!--
                <div class="w-6 h-6 bg-blue-800 text-white rounded-full flex justify-center items-center text-xs">

                </div> -->
            </div>
        </div>

        <p class="text-sm mb-4">
            Your application was successfully submitted!
        </p>

        <p class="text-sm text-red-500 mb-8 cursor-pointer" onclick="backStart()">
            Back to your saved listings
        </p>

        <button onclick="goStep2()"
                class="bg-red-500 text-white px-24 py-2 rounded text-sm">
            Edit application
        </button>

    </div>

</div>

<!-- ================= JS ================= -->

<script>

    const step1 = document.getElementById("step1");
    const step2 = document.getElementById("step2");
    const step3 = document.getElementById("step3");

    const p1 = document.getElementById("p1");
    const p2 = document.getElementById("p2");
    const p3 = document.getElementById("p3");

    const fileInput = document.getElementById("fileInput");
    const fileNameField = document.getElementById("fileNameField");
    const uploadBtn = document.getElementById("uploadBtn");
    const resubmitBtn = document.getElementById("resubmitBtn");

    function goStep2() {
        step1.classList.add("hidden");
        step2.classList.remove("hidden");
        step3.classList.add("hidden");

        p1.className = "w-8 h-8 bg-gray-300 rounded-full flex justify-center items-center";
        p2.className = "w-8 h-8 bg-blue-800 text-white rounded-full flex justify-center items-center";
        p3.className = "w-8 h-8 bg-gray-300 rounded-full flex justify-center items-center";
    }

    function goStep3() {
        step2.classList.add("hidden");
        step3.classList.remove("hidden");

        p2.className = "w-8 h-8 bg-gray-300 rounded-full flex justify-center items-center";
        p3.className = "w-8 h-8 bg-blue-800 text-white rounded-full flex justify-center items-center";
    }

    function backStart() {
        step1.classList.remove("hidden");
        step2.classList.add("hidden");
        step3.classList.add("hidden");

        p1.className = "w-8 h-8 bg-blue-800 text-white rounded-full flex justify-center items-center";
        p2.className = "w-8 h-8 bg-gray-300 rounded-full flex justify-center items-center";
        p3.className = "w-8 h-8 bg-gray-300 rounded-full flex justify-center items-center";
    }

    function fillFileName() {

        if (fileInput.files.length > 0) {
            fileNameField.value = fileInput.files[0].name;

            // Enable upload
            uploadBtn.disabled = false;
            uploadBtn.classList.remove("bg-red-300", "cursor-not-allowed");
            uploadBtn.classList.add("bg-red-500");

        } else {
            resetButtons();
        }

    }

    // Simulate upload click
    uploadBtn.addEventListener("click", function () {

        // Enable resubmit after upload
        resubmitBtn.disabled = false;
        resubmitBtn.classList.remove("bg-red-300", "cursor-not-allowed");
        resubmitBtn.classList.add("bg-red-500");

    });

    function resetButtons() {
        uploadBtn.disabled = true;
        uploadBtn.className = "bg-red-300 text-white px-4 py-2 rounded text-sm cursor-not-allowed";

        resubmitBtn.disabled = true;
        resubmitBtn.className = "bg-red-300 text-white px-10 py-2 rounded cursor-not-allowed";
    }

    function submitForm() {

        // Submit backend form
        document.getElementById("step2").submit();

        // Show step 3 UI
        goStep3();
    }
</script>
</body>
</html>
