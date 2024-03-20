/**
 * Function called when the page is loaded.
 * Configures necessary events after DOM loading.
 */
function onLoad() {
    let element = document.querySelector(".vanish");
    if (element !== null)
        element.onanimationend = function () {
            this.style['display'] = 'none';
        }
}

/**
 * Function called when an element is dragged over a drop zone.
 * Checks if dropping is allowed based on the dragged element and drop zone.
 * @param {Event} ev - The drag and drop event.
 * @param {number} target_id - The ID of the drop zone.
 */
function allowDrop(ev, target_id) {
    let data = [dragOrigin, dragContent];
    if (target_id === data[0]) { // not moved
        ev.dataTransfer.dropEffect = 'none';
        return;
    }
    if ((data[0] === -1) && (target_id !== -1)) {
        ev.preventDefault(); // allow dropping from dive to dive group
        ev.dataTransfer.dropEffect = 'link';
    } else if ((data[0] !== -1) && (target_id === -1)) {
        ev.preventDefault(); // allow removing from dive group to dive
        ev.dataTransfer.dropEffect = 'move';
    } else if ((data[0] !== -1) && (target_id !== data[0])) {
        ev.preventDefault(); // allow moving between dive groups
        ev.dataTransfer.dropEffect = 'move';
    } else {
        ev.dataTransfer.dropEffect = 'none';
    }
}

/**
 * Function called when an element is being dragged.
 * @param {Event} ev - The drag and drop event.
 * @param {number} origin_id - The ID of the origin of the dragged element.
 * @param {number} content_id - The ID of the dragged content.
 */
function drag(ev, origin_id, content_id) {
    let data = "" + origin_id + " " + content_id;
    dragOrigin = origin_id;
    dragContent = content_id;
    ev.dataTransfer.setData("text", data);
    ev.dataTransfer.effectAllowed = 'all';
}

/**
 * Function called when an element is dropped.
 * @param {Event} ev - The drag and drop event.
 * @param {number} target_id - The ID of the drop zone.
 */
function drop(ev, target_id) {
    let data = [dragOrigin, dragContent];
    if ((data[0] === -1) && (target_id !== -1)) {
        ev.preventDefault(); // allow dropping from dive to dive group
        addToPalanquee(data[1], target_id).then(() => location.reload()).catch(r => displayError(r));
    }
    else if ((data[0] !== -1) && (target_id === -1)) {
        ev.preventDefault(); // allow removing from dive group to dive
        removeFromPalanquee(data[1]).then(() => location.reload()).catch(r => displayError(r));
    }
    else if ((data[0] !== -1) && (target_id !== -1)) {
        moveBetweenPalanquees(data[1], data[0], target_id).then(() => location.reload()).catch(r => displayError(r));
    }
}

/**
 * Adds a member to a dive group.
 * @param {number} adherent_id - The ID of the member to add.
 * @param {number} palanquee_id - The ID of the dive group to add the member to.
 * @returns {Promise} - A promise resolved when the request is successful, rejected with an error message otherwise.
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
 * Removes a member from a dive group.
 * @param {number} member_id - The ID of the member to remove.
 * @returns {Promise} - A promise resolved when the request is successful, rejected with an error message otherwise.
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
 * Moves a member between dive groups.
 * @param {number} member_id - The ID of the member to move.
 * @param {number} fromPalanquee_id - The ID of the original dive group of the member.
 * @param {number} toPalanquee_id - The ID of the destination dive group of the member.
 * @returns {Promise} - A promise resolved when the request is successful, rejected with an error message otherwise.
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
 * Displays an error.
 * @param {Object} json_error - The JSON object representing the error.
 */
function displayError(json_error) {
    console.log(json_error); // TODO: Handle error display appropriately
}
