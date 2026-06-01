<x-layouts::app :title="__('Edit Inspection Report')">
    <flux:main class="max-w-3xl space-y-6">
        <div>
            <flux:heading size="xl">Edit {{ $inspection->type_label }} Report</flux:heading>
            <flux:text class="mt-1 text-zinc-500">
                {{ $inspection->lease->tenant->user->name }} · {{ $inspection->lease->unit->unit_number }}
            </flux:text>
        </div>

        <form method="POST" action="{{ route('inspections.update', $inspection) }}"
              class="space-y-6"
              x-data="editInspectionForm({{ json_encode($inspection->items->map(fn($i) => ['id' => $i->id, 'category' => $i->category, 'item_name' => $i->item_name, 'condition' => $i->condition, 'notes' => $i->notes ?? ''])) }})">
            @csrf
            @method('PUT')

            <flux:field>
                <flux:label>{{ __('General Notes') }}</flux:label>
                <flux:textarea name="notes" rows="2">{{ old('notes', $inspection->notes) }}</flux:textarea>
            </flux:field>

            <div>
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Inspection Items') }}</flux:heading>
                    <flux:button type="button" size="sm" icon="plus" variant="ghost" @click="addItem()">
                        {{ __('Add Item') }}
                    </flux:button>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id || ''">
                            <div class="mb-2 flex justify-between">
                                <p class="text-sm font-medium" x-text="'Item #' + (index + 1)"></p>
                                <flux:button type="button" size="xs" variant="ghost" icon="trash-2" @click="removeItem(index)"></flux:button>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:field>
                                    <flux:label>Category</flux:label>
                                    <flux:input :name="'items[' + index + '][category]'" x-model="item.category" required />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Item Name</flux:label>
                                    <flux:input :name="'items[' + index + '][item_name]'" x-model="item.item_name" required />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Condition</flux:label>
                                    <flux:select :name="'items[' + index + '][condition]'" x-model="item.condition">
                                        <flux:option value="good">Good</flux:option>
                                        <flux:option value="fair">Fair</flux:option>
                                        <flux:option value="damaged">Damaged</flux:option>
                                        <flux:option value="missing">Missing</flux:option>
                                    </flux:select>
                                </flux:field>
                                <flux:field>
                                    <flux:label>Notes</flux:label>
                                    <flux:input :name="'items[' + index + '][notes]'" x-model="item.notes" />
                                </flux:field>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Changes') }}</flux:button>
                <flux:button href="{{ route('inspections.show', $inspection) }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>

        <script>
        function editInspectionForm(existingItems) {
            return {
                items: existingItems,
                addItem() { this.items.push({ id: null, category: '', item_name: '', condition: 'good', notes: '' }); },
                removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1); }
            }
        }
        </script>
    </flux:main>
</x-layouts::app>
