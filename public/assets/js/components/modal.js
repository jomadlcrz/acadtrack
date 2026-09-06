// Modal Component JS
function createModal(id, title, content) {
    const modal = document.createElement('div');
    modal.id = id;
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="closeModal('${id}')"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close" onclick="closeModal('${id}')">&times;</button>
            </div>
            <div class="modal-body">${content}</div>
        </div>
    `;
    document.body.appendChild(modal);
    return modal;
}
