
function openModal() {
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('memoryForm').reset();
    document.getElementById('preview').innerHTML = '';
}

document.getElementById('media').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');
    preview.innerHTML = '';

    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const isVideo = file.type.startsWith('video/');
            const elem = document.createElement(isVideo ? 'video' : 'img');
            elem.src = event.target.result;
            if (isVideo) {
                elem.controls = true;
            }
            preview.appendChild(elem);
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('memoryForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const mediaFile = document.getElementById('media').files[0];

    const card = document.createElement('div');
    card.className = 'memory-card';
    card.style.animation = 'slideIn 0.6s ease-out';

    let mediaHTML = '';
    if (mediaFile) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const isVideo = mediaFile.type.startsWith('video/');
            if (isVideo) {
                mediaHTML = `<div class="media-container"><video controls><source src="${event.target.result}" type="${mediaFile.type}"></video></div>`;
            } else {
                mediaHTML = `<div class="media-container"><img src="${event.target.result}" alt="${title}"></div>`;
            }
            card.innerHTML = `
                <h3>${title}</h3>
                <p>${description}</p>
                ${mediaHTML}
            `;
        };
        reader.readAsDataURL(mediaFile);
    } else {
        card.innerHTML = `
            <h3>${title}</h3>
            <p>${description}</p>
        `;
    }

    document.getElementById('timeline').appendChild(card);
    closeModal();
});

window.onclick = function(event) {
    const modal = document.getElementById('modal');
    if (event.target === modal) {
        closeModal();
    }
};