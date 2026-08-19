import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useTaskStore = defineStore('task', () => {
  // State
  const boards = ref([]);
  const activeBoardId = ref(null);
  const tasks = ref([]);
  const assignees = ref([]);
  const loading = ref(false);
  const isAdminView = ref(true);

  // Getters
  const activeBoard = computed(() => {
    return boards.value.find(b => b.id === activeBoardId.value) || boards.value[0] || null;
  });

  const columns = computed(() => {
    return activeBoard.value ? (activeBoard.value.columns || []) : [];
  });

  const tasksByColumn = computed(() => {
    const grouped = {};
    columns.value.forEach(col => {
      grouped[col.id] = tasks.value.filter(t => t.task_column_id === col.id);
    });
    return grouped;
  });

  // Actions
  const fetchBoards = async () => {
    loading.value = true;
    try {
      const response = await axios.get('/api/tasks/boards');
      if (response.data && response.data.boards) {
        boards.value = response.data.boards;
        if (!activeBoardId.value && boards.value.length > 0) {
          activeBoardId.value = boards.value[0].id;
        }
      }
    } catch (error) {
      console.error('Error fetching task boards:', error);
    } finally {
      loading.value = false;
    }
  };

  const selectBoard = async (boardId) => {
    activeBoardId.value = boardId;
    await fetchTasks();
  };

  const createBoard = async (boardData) => {
    loading.value = true;
    try {
      const response = await axios.post('/api/tasks/boards', boardData);
      if (response.data && response.data.board) {
        boards.value.push(response.data.board);
        activeBoardId.value = response.data.board.id;
        await fetchTasks();
      }
      return response.data;
    } catch (error) {
      console.error('Error creating task board:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const deleteBoard = async (boardId) => {
    try {
      await axios.delete(`/api/tasks/boards/${boardId}`);
      boards.value = boards.value.filter(b => b.id !== boardId);
      if (activeBoardId.value === boardId) {
        activeBoardId.value = boards.value.length > 0 ? boards.value[0].id : null;
        await fetchTasks();
      }
    } catch (error) {
      console.error('Error deleting board:', error);
      throw error;
    }
  };

  const fetchTasks = async () => {
    if (!activeBoardId.value) {
      tasks.value = [];
      return;
    }

    loading.value = true;
    try {
      const response = await axios.get('/api/tasks', {
        params: { board_id: activeBoardId.value }
      });
      if (response.data) {
        tasks.value = response.data.tasks || [];
        isAdminView.value = !!response.data.is_admin_view;
      }
    } catch (error) {
      console.error('Error fetching tasks:', error);
    } finally {
      loading.value = false;
    }
  };

  const createTask = async (taskData) => {
    try {
      let payload = taskData;
      const targetBoardId = taskData.task_board_id || activeBoardId.value || (boards.value[0] ? boards.value[0].id : null);
      
      if (!(taskData instanceof FormData)) {
        payload = new FormData();
        if (targetBoardId) {
          payload.append('task_board_id', targetBoardId);
        }
        Object.keys(taskData).forEach(key => {
          if (key === 'attachments') {
            (taskData.attachments || []).forEach(file => {
              payload.append('attachments[]', file);
            });
          } else if (key === 'assignee_ids' || key === 'tags') {
            payload.append(key, JSON.stringify(taskData[key] || []));
          } else if (taskData[key] !== null && taskData[key] !== undefined) {
            payload.append(key, taskData[key]);
          }
        });
      } else {
        if (targetBoardId && !payload.has('task_board_id')) {
          payload.append('task_board_id', targetBoardId);
        }
      }

      const response = await axios.post('/api/tasks', payload);
      if (response.data && response.data.task) {
        tasks.value.push(response.data.task);
      }
      return response.data;
    } catch (error) {
      console.error('Error creating task:', error);
      throw error;
    }
  };

  const updateTask = async (taskId, taskData) => {
    try {
      let payload = taskData;
      if (!(taskData instanceof FormData)) {
        payload = new FormData();
        payload.append('_method', 'PUT');
        Object.keys(taskData).forEach(key => {
          if (key === 'attachments') {
            (taskData.attachments || []).forEach(file => {
              payload.append('attachments[]', file);
            });
          } else if (key === 'assignee_ids' || key === 'tags' || key === 'deleted_attachment_ids') {
            payload.append(key, JSON.stringify(taskData[key] || []));
          } else if (taskData[key] !== null && taskData[key] !== undefined) {
            payload.append(key, taskData[key]);
          }
        });
      }

      const response = await axios.post(`/api/tasks/${taskId}`, payload);
      if (response.data && response.data.task) {
        const idx = tasks.value.findIndex(t => t.id === taskId);
        if (idx > -1) {
          tasks.value[idx] = response.data.task;
        }
      }
      return response.data;
    } catch (error) {
      console.error('Error updating task:', error);
      throw error;
    }
  };

  const moveTask = async (taskId, newColumnId, newOrder = 0) => {
    // Optimistic UI update
    const task = tasks.value.find(t => t.id === taskId);
    if (task) {
      task.task_column_id = newColumnId;
    }

    try {
      const response = await axios.post(`/api/tasks/${taskId}/move`, {
        task_column_id: newColumnId,
        order: newOrder,
      });
      if (response.data && response.data.task) {
        const idx = tasks.value.findIndex(t => t.id === taskId);
        if (idx > -1) {
          tasks.value[idx] = response.data.task;
        }
      }
    } catch (error) {
      console.error('Error moving task:', error);
      // Revert if error
      await fetchTasks();
    }
  };

  const deleteTask = async (taskId) => {
    try {
      await axios.delete(`/api/tasks/${taskId}`);
      tasks.value = tasks.value.filter(t => t.id !== taskId);
    } catch (error) {
      console.error('Error deleting task:', error);
      throw error;
    }
  };

  const fetchAssignees = async () => {
    try {
      const response = await axios.get('/api/tasks/assignees');
      if (response.data && response.data.assignees) {
        assignees.value = response.data.assignees;
      }
    } catch (error) {
      console.error('Error fetching assignees:', error);
    }
  };

  const createColumn = async (boardId, columnData) => {
    try {
      const response = await axios.post(`/api/tasks/boards/${boardId}/columns`, columnData);
      if (response.data && response.data.column) {
        const board = boards.value.find(b => b.id === boardId);
        if (board) {
          if (!board.columns) board.columns = [];
          board.columns.push(response.data.column);
        }
      }
      return response.data;
    } catch (error) {
      console.error('Error creating column:', error);
      throw error;
    }
  };

  const performBulkAction = async (payload) => {
    try {
      const response = await axios.post('/api/tasks/bulk-action', payload);
      await fetchTasks();
      return response.data;
    } catch (error) {
      console.error('Error performing bulk action:', error);
      throw error;
    }
  };

  const toggleStarTask = async (taskId) => {
    try {
      const targetTask = tasks.value.find(t => t.id === taskId);
      if (targetTask) {
        targetTask.is_starred = !targetTask.is_starred;
      }
      const response = await axios.post(`/api/tasks/${taskId}/toggle-star`);
      if (response.data && response.data.is_starred !== undefined && targetTask) {
        targetTask.is_starred = response.data.is_starred;
      }
      return response.data;
    } catch (error) {
      console.error('Error toggling task star:', error);
      throw error;
    }
  };

  const addTaskComment = async (taskId, commentText) => {
    try {
      const response = await axios.post(`/api/tasks/${taskId}/comments`, { comment: commentText });
      const targetTask = tasks.value.find(t => t.id === taskId);
      if (targetTask && response.data && response.data.comment) {
        if (!targetTask.comments) targetTask.comments = [];
        targetTask.comments.unshift(response.data.comment);
      }
      return response.data;
    } catch (error) {
      console.error('Error adding task comment:', error);
      throw error;
    }
  };

  return {
    boards,
    activeBoardId,
    activeBoard,
    columns,
    tasks,
    tasksByColumn,
    assignees,
    loading,
    isAdminView,
    fetchBoards,
    selectBoard,
    createBoard,
    deleteBoard,
    fetchTasks,
    createTask,
    updateTask,
    moveTask,
    deleteTask,
    fetchAssignees,
    createColumn,
    performBulkAction,
    toggleStarTask,
    addTaskComment,
  };
});
