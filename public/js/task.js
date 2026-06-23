(function () {
    var board = document.querySelector('.task-board');

    if (!board) {
        return;
    }

    var draggedCard = null;
    var originalColumn = null;

    function findColumn(element) {
        return element ? element.closest('.task-column') : null;
    }

    function saveStatus(taskId, statusId) {
        var body = new URLSearchParams();
        body.append('task_id', taskId);
        body.append('task_status_id', statusId);

        board.classList.add('task-is-saving');

        fetch(board.dataset.updateStatusUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: body.toString(),
            credentials: 'same-origin'
        }).then(function () {
            window.location.reload();
        }).catch(function () {
            if (originalColumn && draggedCard) {
                originalColumn.appendChild(draggedCard);
            }

            board.classList.remove('task-is-saving');
        });
    }

    board.addEventListener('dragstart', function (event) {
        var card = event.target.closest('.task-card');

        if (!card) {
            return;
        }

        draggedCard = card;
        originalColumn = findColumn(card);

        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', card.dataset.taskId);

        window.setTimeout(function () {
            card.classList.add('task-dragging');
        }, 0);
    });

    board.addEventListener('dragend', function () {
        if (draggedCard) {
            draggedCard.classList.remove('task-dragging');
        }

        document.querySelectorAll('.task-drop-target').forEach(function (column) {
            column.classList.remove('task-drop-target');
        });
    });

    board.addEventListener('dragover', function (event) {
        var column = findColumn(event.target);

        if (!column || !draggedCard) {
            return;
        }

        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        column.classList.add('task-drop-target');
    });

    board.addEventListener('dragleave', function (event) {
        var column = findColumn(event.target);

        if (column && !column.contains(event.relatedTarget)) {
            column.classList.remove('task-drop-target');
        }
    });

    board.addEventListener('drop', function (event) {
        var column = findColumn(event.target);

        if (!column || !draggedCard) {
            return;
        }

        event.preventDefault();
        column.classList.remove('task-drop-target');

        var oldStatusId = originalColumn ? originalColumn.dataset.statusId : null;
        var newStatusId = column.dataset.statusId;

        if (oldStatusId === newStatusId) {
            return;
        }

        column.appendChild(draggedCard);
        saveStatus(draggedCard.dataset.taskId, newStatusId);
    });
}());
