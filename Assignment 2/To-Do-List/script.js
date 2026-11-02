// 1. Select DOM elements from our HTML
const taskInput = document.getElementById("task-input");
const addBtn = document.getElementById("add-btn");
const taskList = document.getElementById("task-list");
const taskCounter = document.getElementById("task-counter");

// 2. Load tasks from localStorage (or use an empty array if nothing is saved)
let tasks = JSON.parse(localStorage.getItem("tasks")) || [];

// 3. Function to save current tasks array to localStorage
function saveTasks() {
  localStorage.setItem("tasks", JSON.stringify(tasks));
}

// 4. Function to update the task counter display
function updateCounter() {
  const pendingTasks = tasks.filter(task => !task.completed).length;
  taskCounter.textContent = `${pendingTasks} task${pendingTasks === 1 ? "" : "s"} remaining`;
}

// 5. Function to render tasks onto the screen
function renderTasks() {
  // Clear the existing list items first
  taskList.innerHTML = "";

  // If no tasks exist, show a friendly message
  if (tasks.length === 0) {
    const emptyMsg = document.createElement("li");
    emptyMsg.className = "empty-msg";
    emptyMsg.textContent = "No tasks yet. Add one above!";
    taskList.appendChild(emptyMsg);
    updateCounter();
    return;
  }

  // Loop through tasks and create HTML elements for each
  tasks.forEach((task, index) => {
    const li = document.createElement("li");
    li.className = `task-item ${task.completed ? "completed" : ""}`;

    // Left container for checkbox and task text
    const contentDiv = document.createElement("div");
    contentDiv.className = "task-content";

    // Checkbox to mark complete/incomplete
    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "task-checkbox";
    checkbox.checked = task.completed;
    checkbox.addEventListener("change", () => toggleTask(index));

    // Task text span
    const textSpan = document.createElement("span");
    textSpan.className = "task-text";
    textSpan.textContent = task.text;

    contentDiv.appendChild(checkbox);
    contentDiv.appendChild(textSpan);

    // Delete button
    const deleteBtn = document.createElement("button");
    deleteBtn.className = "delete-btn";
    deleteBtn.textContent = "Delete";
    deleteBtn.addEventListener("click", () => deleteTask(index));

    // Append both parts to list item
    li.appendChild(contentDiv);
    li.appendChild(deleteBtn);

    // Append list item to the list
    taskList.appendChild(li);
  });

  updateCounter();
}

// 6. Function to add a new task
function addTask() {
  const taskText = taskInput.value.trim();

  // Validate: don't add empty tasks
  if (taskText === "") {
    alert("Please enter a task!");
    return;
  }

  // Create task object and add to array
  const newTask = {
    text: taskText,
    completed: false
  };

  tasks.push(newTask);

  // Save to localStorage & refresh display
  saveTasks();
  renderTasks();

  // Clear input field and focus back
  taskInput.value = "";
  taskInput.focus();
}

// 7. Function to toggle task completion status
function toggleTask(index) {
  tasks[index].completed = !tasks[index].completed;
  saveTasks();
  renderTasks();
}

// 8. Function to delete a task
function deleteTask(index) {
  // Remove 1 item at the given index
  tasks.splice(index, 1);
  saveTasks();
  renderTasks();
}

// 9. Event Listeners
addBtn.addEventListener("click", addTask);

// Allow adding task by pressing Enter key
taskInput.addEventListener("keydown", (event) => {
  if (event.key === "Enter") {
    addTask();
  }
});

// 10. Initial render on page load
renderTasks();
