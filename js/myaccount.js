$(document).ready(function () {

    /* ── localStorage helpers ─────────────────── */
    function getProfile() {
        return JSON.parse(localStorage.getItem('userProfile') || '{}');
    }

    function saveProfile(profile) {
        localStorage.setItem('userProfile', JSON.stringify(profile));
    }

    /* ── Populate profile on load ─────────────── */
    function loadProfile() {
        var profile = getProfile();
        if (profile.username) {
            $('#username').text(profile.username);
        }
        if (profile.image) {
            $('#profile-pic img').attr('src', profile.image);
        }
    }

    loadProfile();

    /* ── Edit button → modal ─────────────────── */
    var currentImageData = null;

    $('#edit-btn').on('click', function () {
        var profile = getProfile();
        $('#edit-username').val(profile.username || $('#username').text());
        $('#edit-image').val('');
        currentImageData = null;

        if (profile.image) {
            $('#edit-img-preview').attr('src', profile.image).show();
        } else {
            $('#edit-img-preview').hide().attr('src', '');
        }

        $('#profile-modal').fadeIn(200);
    });

    /* Image preview */
    $('#edit-image').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            currentImageData = e.target.result;
            $('#edit-img-preview').attr('src', currentImageData).show();
        };
        reader.readAsDataURL(file);
    });

    /* Close modal */
    $('#profile-modal-close, #profile-backdrop').on('click', function () {
        $('#profile-modal').fadeOut(180);
    });

    /* Save */
    $('#profile-save').on('click', function () {
        var newUsername = $.trim($('#edit-username').val());
        if (!newUsername) { alert('Username cannot be empty.'); return; }

        var profile = getProfile();
        profile.username = newUsername;
        if (currentImageData) {
            profile.image = currentImageData;
        }
        saveProfile(profile);

        /* Update page live */
        $('#username').text(newUsername);
        if (profile.image) {
            $('#profile-pic img').attr('src', profile.image);
        }

        $('#profile-modal').fadeOut(180);
    });
});
