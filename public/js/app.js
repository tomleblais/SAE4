/**
 * Fonction appelée lors du chargement de la page.
 * Configure les événements nécessaires après le chargement du DOM.
 */
function onLoad() {
    let element = document.querySelector(".vanish");
    if (element !== null)
        element.onanimationend = function () {
            this.style['display'] = 'none';
        }
}

/**
 * Fonction appelée lorsqu'un élément est déplacé au-dessus d'une zone de dépôt.
 * Vérifie si le dépôt est autorisé en fonction de l'élément déplacé et de la zone de dépôt.
 * @param {Event} ev - L'événement de glisser-déposer.
 * @param {number} target_id - L'ID de la zone de dépôt.
 */
function allowDrop(ev, target_id) {
    let data = [dragOrigin, dragContent];
    if (target_id === data[0]) { // non déplacé
        ev.dataTransfer.dropEffect = 'none';
        return;
    }
    if ((data[0] === -1) && (target_id !== -1)) {
        ev.preventDefault(); // autoriser le dépôt de la plongée à la palanquée
        ev.dataTransfer.dropEffect = 'link';
    } else if ((data[0] !== -1) && (target_id === -1)) {
        ev.preventDefault(); // autoriser le retrait de la palanquée à la plongée
        ev.dataTransfer.dropEffect = 'move';
    } else if ((data[0] !== -1) && (target_id !== data[0])) {
        ev.preventDefault(); // autoriser le déplacement entre les palanquées
        ev.dataTransfer.dropEffect = 'move';
    } else {
        ev.dataTransfer.dropEffect = 'none';
    }
}

/**
 * Fonction appelée lorsqu'un élément est déplacé.
 * @param {Event} ev - L'événement de glisser-déposer.
 * @param {number} origin_id - L'ID de l'origine de l'élément déplacé.
 * @param {number} content_id - L'ID du contenu déplacé.
 */
function drag(ev, origin_id, content_id) {
    let data = "" + origin_id + " " + content_id;
    dragOrigin = origin_id;
    dragContent = content_id;
    ev.dataTransfer.setData("text", data);
    ev.dataTransfer.effectAllowed = 'all';
}

/**
 * Fonction appelée lorsqu'un élément est déposé.
 * @param {Event} ev - L'événement de glisser-déposer.
 * @param {number} target_id - L'ID de la zone de dépôt.
 */
function drop(ev, target_id) {
    let data = [dragOrigin, dragContent];
    if ((data[0] === -1) && (target_id !== -1)) {
        ev.preventDefault(); // autoriser le dépôt de la plongée à la palanquée
        addToPalanquee(data[1], target_id).then(() => location.reload()).catch(r => displayError(r));
    }
    else if ((data[0] !== -1) && (target_id === -1)) {
        ev.preventDefault(); // autoriser le retrait de la palanquée à la plongée
        removeFromPalanquee(data[1]).then(() => location.reload()).catch(r => displayError(r));
    }
    else if ((data[0] !== -1) && (target_id !== -1)) {
        moveBetweenPalanquees(data[1], data[0], target_id).then(() => location.reload()).catch(r => displayError(r));
    }
}

/**
 * Ajoute un adhérent à une palanquée.
 * @param {number} adherent_id - L'ID de l'adhérent à ajouter.
 * @param {number} palanquee_id - L'ID de la palanquée à laquelle ajouter l'adhérent.
 * @returns {Promise} - Une promesse résolue lorsque la requête est réussie, rejetée avec un message d'erreur sinon.
 */
async function addToPalanquee(adherent_id, palanquee_id) {
    const res = await fetch("/api/palanquees/" + palanquee_id + "/membres", {
        method: "POST",
        headers: {"Content-Type": "application/json", "Accept": "application/json"},
        mode: "cors",
        body: JSON.stringify({adherent: adherent_id, palanquee: palanquee_id}),
    });
    if (res.ok) {
        return res.json();
    } else {
        throw await res.json();
    }
}

/**
 * Retire un membre d'une palanquée.
 * @param {number} member_id - L'ID du membre à retirer.
 * @returns {Promise} - Une promesse résolue lorsque la requête est réussie, rejetée avec un message d'erreur sinon.
 */
async function removeFromPalanquee(member_id) {
    const res = await fetch("/api/palanquees/membres/" + member_id, {
        method: "DELETE",
        headers: {"Accept": "application/json"},
        mode: "cors",
    });
    if (res.ok) {
        return res.statusText;
    } else {
        throw await res.json();
    }
}

/**
 * Déplace un membre entre les palanquées.
 * @param {number} member_id - L'ID du membre à déplacer.
 * @param {number} fromPalanquee_id - L'ID de la palanquée d'origine du membre.
 * @param {number} toPalanquee_id - L'ID de la palanquée de destination du membre.
 * @returns {Promise} - Une promesse résolue lorsque la requête est réussie, rejetée avec un message d'erreur sinon.
 */
async function moveBetweenPalanquees(member_id, fromPalanquee_id, toPalanquee_id) {
    const res = await fetch("/api/palanquees/membres/" + member_id, {
        method: "PUT",
        headers: {"Content-Type": "application/json", "Accept": "application/json"},
        mode: "cors",
        body: JSON.stringify({ palanquee: toPalanquee_id }),
    });
    if (res.ok) {
        return res.json();
    } else {
        throw await res.json();
    }
}

/**
 * Affiche une erreur.
 * @param {Object} json_error - L'objet JSON représentant l'erreur.
 */
function displayError(json_error) {
    console.log(json_error); // TODO: Gérer l'affichage de l'erreur de manière appropriée
}

