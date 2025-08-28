function addLieu() {
    const lieuSelect = document.getElementById('sortie_lieu');
    const lieuForm = document.getElementById('lieu-form');
    const modalElement = document.getElementById('modalCreateLieu');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    if (!lieuForm) {
        console.warn('Élément #lieu-form introuvable dans le DOM.');
        return;
    }

    lieuForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(lieuForm);

        fetch('/sortie/lieu/add', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.text = data.nom;
                    option.selected = true;

                    if (lieuSelect) {
                        lieuSelect.appendChild(option);
                        lieuSelect.dispatchEvent(new Event('change'));
                    }

                    modal.hide();
                } else {
                    alert('Erreur lors de l’ajout du lieu : ' + (data.errors || 'Inconnue'));
                    modal.hide();

                }
            })
            .catch(err => {
                console.error('Erreur AJAX:', err);
                alert('Une erreur est survenue');
                modal.hide();
            });
    });
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', addLieu);
} else {
    addLieu();
}