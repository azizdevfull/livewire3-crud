<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TodoList extends Component
{
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name;

    public $search;

    public $editingTodoId;

    #[Rule('required|min:3|max:50')]
    public $editingTodoName;
    public function create()
    {
        $validated = $this->validateOnly('name');

        Todo::create($validated);

        $this->reset('name');

        session()->flash('success', 'Created.');

        $this->resetPage();
    }
    public function toggle($id)
    {
        $todo = Todo::findOrFail($id);
        $todo->completed = !$todo->completed;
        $todo->save();
    }
    public function delete($id)
    {
        try {
            Todo::findOrFail($id)->delete();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete Todo!');
            return;
        }
    }

    public function edit($id)
    {
        $this->editingTodoId = $id;
        $this->editingTodoName = Todo::findOrFail($id)->name;
    }
    public function cancelEdit()
    {
        $this->reset('editingTodoId', 'editingTodoName');
    }
    public function update()
    {
        $this->validateOnly('editingTodoName');
        $todo = Todo::findOrFail($this->editingTodoId);
        $todo->name = $this->editingTodoName;
        $todo->update();

        $this->cancelEdit();
    }
    public function render()
    {
        $todos = Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(5);
        return view('livewire.todo-list', compact('todos'));
    }
}
