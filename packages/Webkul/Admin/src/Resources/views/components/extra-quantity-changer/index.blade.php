@props([
    'name' => '',
    'value' => 1,
])

<v-extra-quantity-changer {{ $attributes->merge(['class' => 'flex items-center border dark:border-gray-300']) }}
    name="{{ $name }}" value="{{ $value }}">
</v-extra-quantity-changer>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-extra-quantity-changer-template"
    >
        <div>
            <span
                class="cursor-pointer text-2xl dark:text-gray-300"
                @click="decrease"
            >
                -
            </span>

            {{-- <p class="w-2.5 select-none text-center dark:text-gray-300">
                @{{ quantity }}
            </p> --}}

            <v-field
                type="number"
                :name="name"
                v-model="quantity"
            ></v-field>

            <span
                class="cursor-pointer text-2xl dark:text-gray-300"
                @click="increase"
            >
                +
            </span>

            {{-- <v-field
                type="number"
                :name="name"
                v-model="quantity"
            ></v-field> --}}
        </div>
    </script>

    <script type="module">
        app.component("v-extra-quantity-changer", {
            template: '#v-extra-quantity-changer-template',

            props: ['name', 'value'],

            data() {
                return {
                    quantity: this.value,
                }
            },

            watch: {
                value() {
                    this.quantity = this.value;
                },

                quantity(newQuantity) {
                    this.quantity = newQuantity;
                    this.$emit('change', newQuantity);
                },
            },

            methods: {
                increase() {
                    this.$emit('change', ++this.quantity);
                },

                decrease() {
                    if (this.quantity > 0) {
                        this.quantity -= 1;

                        this.$emit('change', this.quantity);
                    }
                },
            }
        });
    </script>
@endpushOnce
