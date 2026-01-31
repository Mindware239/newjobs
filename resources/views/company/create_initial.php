<div class="container">
    <h2>Welcome! Create Your Company Profile</h2>
    <p class="text-gray-600">This is the public page where job seekers will see your brand.</p>

    <form action="/employer/company/store" method="POST">
        
        <label for="short_name">Company Name (Required):</label>
        <input type="text" id="short_name" name="short_name" required>
        
        <label for="website">Official Website URL (Required):</label>
        <input type="url" id="website" name="website" required>

        <label for="headquarters">Headquarters Location:</label>
        <input type="text" id="headquarters" name="headquarters">
        
        <label for="description">Short Description:</label>
        <textarea id="description" name="description" rows="4"></textarea>

        <button type="submit">Create Profile</button>
    </form>
</div>