<x-layouts::app :title="__('New Inspection Report')">
    <flux:main class="mx-auto w-1/2 space-y-6">
        <div>
            <flux:heading size="xl">{{ __('New Inspection Report') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500">{{ __('Document the condition of the unit at move-in or move-out.') }}</flux:text>
        </div>

        <form method="POST" action="{{ route('inspections.store') }}" enctype="multipart/form-data"
              class="space-y-6"
              x-data="inspectionForm()">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Lease') }}</flux:label>
                    <flux:select name="lease_id" required>
                        <option value="">{{ __('Select lease…') }}</option>
                        @foreach ($leases as $lease)
                            <option value="{{ $lease->id }}"  @selected(old('lease_id', $preselectedLeaseId) == $lease->id)>
                                {{ $lease->tenant->user->name }} — {{ $lease->unit->unit_number }} ({{ $lease->unit->property->name }})
                            </option>
                        @endforeach
                    </flux:select>
                    @error('lease_id') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Report Type') }}</flux:label>
                    <flux:select name="type" required>
                        <option value="move_in"  @selected(old('type') === 'move_in')>Move-In</option>
                        <option value="move_out"  @selected(old('type') === 'move_out')>Move-Out</option>
                    </flux:select>
                    @error('type') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>
            </div>

            <flux:field>
                <flux:label aside="{{ __('Optional') }}">{{ __('General Notes') }} </flux:label>
                <flux:textarea name="notes" rows="2">{{ old('notes') }}</flux:textarea>
            </flux:field>

            {{-- Inspection Items --}}
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">{{ __('Inspection Items') }}</flux:heading>
                    <flux:button type="button" size="sm" icon="plus" variant="ghost" @click="addItem()">
                        {{ __('Add Item') }}
                    </flux:button>
                </div>

                @error('items') <flux:error class="mb-2">{{ $message }}</flux:error> @enderror

                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300" x-text="'Item #' + (index + 1)"></p>
                                <flux:button type="button" size="xs" variant="ghost" icon="trash" @click="removeItem(index)"></flux:button>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:field>
                                    <flux:label>Category</flux:label>
                                    <flux:input x-bind:name="'items[' + index + '][category]'" x-model="item.category"
                                        placeholder="e.g. Kitchen, Bathroom, Living Room" required />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Item Name</flux:label>
                                    <flux:input x-bind:name="'items[' + index + '][item_name]'" x-model="item.item_name"
                                        placeholder="e.g. Sink, Door, Window" required />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Condition</flux:label>
                                    <flux:select x-bind:name="'items[' + index + '][condition]'" x-model="item.condition">
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="missing">Missing</option>
                                    </flux:select>
                                </flux:field>
                                <flux:field>
                                    <flux:label>Notes <span class="text-zinc-400">(Optional)</span></flux:label>
                                    <flux:input x-bind:name="'items[' + index + '][notes]'" x-model="item.notes" />
                                </flux:field>
                            </div>
                        </div>
                    </template>
                </div>

                <flux:button type="button" variant="ghost" size="sm" icon="plus" class="mt-3 w-full" @click="addItem()">
                    {{ __('Add Another Item') }}
                </flux:button>
            </div>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save Report') }}</flux:button>
                <flux:button href="{{ route('inspections.index') }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>

        <script>
        function inspectionForm() {
            return {
                items: [{ category: '', item_name: '', condition: 'good', notes: '' }],
                addItem() {
                    this.items.push({ category: '', item_name: '', condition: 'good', notes: '' });
                },
                removeItem(index) {
                    if (this.items.length > 1) this.items.splice(index, 1);
                }
            }
        }
        </script>
    </flux:main>
</x-layouts::app>
