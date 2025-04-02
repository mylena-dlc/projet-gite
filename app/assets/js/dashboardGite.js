export function initGiteDashboard() {
    const dashboard = document.getElementById('gite-dashboard');
    if (!dashboard) return;

    const updateUrl = dashboard.dataset.updateUrl;
    const deleteUrlTemplate = dashboard.dataset.deletePictureUrl;

    // -------- MODALE ÉDITION --------
    window.openEditModal = (field, giteId, currentValue) => {
        document.getElementById('field-name').value = field;
        document.getElementById('gite-id').value = giteId;
        document.getElementById('new-value').value = currentValue;
        document.getElementById('editModal').style.display = 'block';
    };

    window.closeEditModal = () => {
        document.getElementById('editModal').style.display = 'none';
    };

    document.getElementById('editForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const field = this.querySelector('#field-name').value;
        const giteId = this.querySelector('#gite-id').value;
        const newValue = this.querySelector('#new-value').value;

        fetch(updateUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: giteId, field, value: newValue })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const formatted = ['price', 'cleaningCharge'].includes(field)
                    ? `${newValue} €`
                    : `${newValue} personnes`;
                document.getElementById(`${field}-value`).innerText = formatted;
                closeEditModal();
            } else {
                alert('Erreur lors de la mise à jour.');
            }
        }).catch(console.error);
    });

    // -------- AJOUT DE CATÉGORIE --------
    document.getElementById('addCategory')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const categoryList = document.querySelector('.category-list');
                const p = document.createElement('p');
                p.innerHTML = `${data.category.name} <a href="/delete-category/${data.category.id}" class="ml-2"><i class="fa-solid fa-trash-can"></i></a>`;
                categoryList.appendChild(p);
                this.reset();
            } else {
                alert('Erreur ajout catégorie');
            }
        }).catch(console.error);
    });

    // -------- AJOUT D’IMAGE --------
    document.getElementById('formPicture')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const gallery = document.querySelector(`.image-gallery[data-category="${data.picture.category}"]`);
                if (gallery) {
                    const div = document.createElement('div');
                    div.className = 'image-item';
                    div.innerHTML = `
                        <img src="/assets/img/${data.picture.url}" alt="${data.picture.alt}" class="w-48 h-48 object-cover rounded-lg">
                    `;
                    gallery.appendChild(div);
                }
                this.reset();
            } else {
                alert(data.message || 'Erreur lors de l\'ajout.');
            }
        }).catch(console.error);
    });

    // -------- SUPPRESSION D’IMAGE --------
    document.querySelectorAll('.delete-picture-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const pictureId = button.getAttribute('data-id');

            if (!confirm('Voulez-vous vraiment supprimer cette image ?')) return;

            const url = deleteUrlTemplate.replace('PICTURE_ID_PLACEHOLDER', pictureId);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    button.closest('.image-item').remove();
                } else {
                    alert('Erreur suppression : ' + data.message);
                }
            }).catch(console.error);
        });
    });
}
