function chargementLieu() {
    const lieuSelect = document.getElementById('sortie_lieu');

    if (lieuSelect) {
        const lieuDetails = document.getElementById('lieu-details');
        lieuSelect.addEventListener('change', function () {

            const lieuId = this.value;

            if (!lieuId) {
                lieuDetails.innerHTML = '';
                return;
            }

            fetch(`/sortie/lieu/details/${lieuId}`)
                .then(response => response.json())
                .then(data => {
                    lieuDetails.innerHTML = `
                    <div>Ville : ${data.ville}</div>
                    <div>Rue : ${data.rue}</div>
                    <div>Code Postal : ${data.codePostal}</div>
                    <div>Latitude : ${data.latitude}</div>
                    <div>Longitude : ${data.longitude}</div>
                    `;
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des détails du lieu:', error);
                });
        });
    } else {
        console.warn('Élément #sortie_lieu introuvable dans le DOM.');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', chargementLieu);
} else {
    chargementLieu();
}



