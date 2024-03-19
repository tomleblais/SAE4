//require('./bootstrap');
let dragOrigin = null;
let dragContent = null;
function onLoad() {
    let element = document.querySelector(".vanish");
    if (element !== null)
        element.onanimationend = function () {
            this.style['display'] = 'none';
        }
}

function allowDrop(ev, target_id) {
    /*
    let raw = ev.dataTransfer.getData("text"); // does not work in chrome
    var data = raw.split(" ");
    */
    let data = [dragOrigin, dragContent];
    //console.log("considering drop of "+data[1]+" from "+ data[0] + " to "+target_id);
    if (target_id === data[0]) { // not moved
        ev.dataTransfer.dropEffect = 'none';
        //console.log("not moved");
        return
    }
    if ((data[0] === -1) && (target_id !== -1)) {
        ev.preventDefault(); // allow drop from dive to palanquee
        //console.log("-> ok add to palanquee")
        ev.dataTransfer.dropEffect = 'link';
    } else if ((data[0] !== -1) && (target_id === -1)) {
        ev.preventDefault(); // allow drop from palanquee to dive
        //console.log("-> ok remove from palanquee")
        ev.dataTransfer.dropEffect = 'move';
    } else if ((data[0] !== -1) && (target_id !== data[0])) {
        ev.preventDefault(); // allow drop from palanquee to other palanquee
        ev.dataTransfer.dropEffect = 'move';
        //console.log("-> ok move between palanquees")
    } else {
        ev.dataTransfer.dropEffect = 'none';
        //console.log("-> not ok")
    }
}

function drag(ev, origin_id, content_id) {
    let data = ""+origin_id+" "+content_id;
    dragOrigin = origin_id;
    dragContent = content_id;
    ev.dataTransfer.setData("text", data);
    ev.dataTransfer.effectAllowed = 'all';

    console.log("dragging "+content_id+" from "+ origin_id +" : "+data)
}

function drop(ev, target_id) {
    //var data = ev.dataTransfer.getData("text").split(" "); // using globals allow for keeping data types
    let data = [dragOrigin, dragContent];
    if ((data[0] === -1) && (target_id !== -1)) {
        ev.preventDefault(); // allow drop from dive to palanquee
        console.log("adding to "+target_id);
        addToPalanquee(data[1], target_id).then(() => location.reload()).catch(r => displayError(r));
    }
    else if ((data[0] !== -1) && (target_id === -1)) {
        ev.preventDefault(); // allow drop from palanquee to dive
        console.log("removing from "+data[0]);
        removeFromPalanquee(data[1]).then(() => location.reload()).catch(r => displayError(r));
    }
    else if ((data[0] !== -1) && (target_id !== -1)) {
        console.log("moving to "+target_id);
        moveBetweenPalanquees(data[1], data[0], target_id).then(() => location.reload()).catch(r => displayError(r));
    }
}

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

function displayError(json_error) {
    console.log(json_error); // TODO
}
