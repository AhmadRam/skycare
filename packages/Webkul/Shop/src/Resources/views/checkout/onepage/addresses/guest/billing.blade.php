<div>
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-medium max-sm:text-xl">
            {{-- @lang('shop::app.checkout.onepage.addresses.billing.billing-address') --}}
            @lang('shop::app.checkout.onepage.addresses.shipping.shipping-address')
        </h2>
    </div>

    <div class="mt-2">
        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.form.before') !!}

        <!-- Company Name -->
        {{-- <x-shop::form.control-group>
            <x-shop::form.control-group.label>
                @lang('shop::app.checkout.onepage.addresses.billing.company-name')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="text"
                name="billing.company_name"
                ::value="guest.cart.billingAddress.companyName"
                :label="trans('shop::app.checkout.onepage.addresses.billing.company-name')"
                :placeholder="trans('shop::app.checkout.onepage.addresses.billing.company-name')"
            />

            <x-shop::form.control-group.error control-name="billing.company_name" />
        </x-shop::form.control-group> --}}

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.company_name.after') !!}

        <!-- First Name -->
        <div class="grid grid-cols-2 gap-x-5">
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.first-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.first_name" ::value="guest.cart.billingAddress.firstName"
                    :label="trans('shop::app.checkout.onepage.addresses.billing.first-name')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.first-name')" />

                <x-shop::form.control-group.error control-name="billing.first_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.first_name.after') !!}

            <!-- Last Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.last-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.last_name" ::value="guest.cart.billingAddress.lastName"
                    :label="trans('shop::app.checkout.onepage.addresses.billing.last-name')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.last-name')" />

                <x-shop::form.control-group.error control-name="billing.last_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.last_name.after') !!}
        </div>

        <!-- Email -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="!mt-0 required">
                @lang('shop::app.checkout.onepage.addresses.billing.email')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="email" name="billing.email" ::value="guest.cart.billingAddress.email"
                {{-- rules="required|email" --}}
                 :label="trans('shop::app.checkout.onepage.addresses.billing.email')" placeholder="email@example.com" />

            <x-shop::form.control-group.error control-name="billing.email" />
        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.email.after') !!}

        <div class="grid grid-cols-2 gap-x-5">
            <!-- Country -->
            <x-shop::form.control-group class="!mb-4">
                <x-shop::form.control-group.label class="{{ core()->isCountryRequired() ? 'required' : '' }} !mt-0">
                    @lang('shop::app.checkout.onepage.addresses.billing.country')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="select" name="billing.country" ::value="guest.cart.billingAddress.country"
                    {{-- rules="{{ core()->isCountryRequired() ? 'required' : '' }}" --}}
                     :label="trans('shop::app.checkout.onepage.addresses.billing.country')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.country')">
                    <option value="">
                        @lang('shop::app.checkout.onepage.addresses.billing.select-country')
                    </option>

                    <option v-for="country in countries" :value="country.code" v-text="country.name">
                    </option>
                </x-shop::form.control-group.control>

                <x-shop::form.control-group.error control-name="billing.country" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.country.after') !!}

            <!-- State -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="{{ core()->isStateRequired() ? 'required' : '' }} !mt-0">
                    @lang('shop::app.checkout.onepage.addresses.billing.state')
                </x-shop::form.control-group.label>

                <template v-if="haveStates(values.billing?.country)">
                    <x-shop::form.control-group.control type="select" name="billing.state"
                        {{-- rules="{{ core()->isStateRequired() ? 'required' : '' }}" --}}
                         :label="trans('shop::app.checkout.onepage.addresses.billing.state')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.state')">
                        <option value="">
                            @lang('shop::app.checkout.onepage.addresses.billing.select-state')
                        </option>

                        <option v-for='(state, index) in states[values.billing?.country]' :value="state.code">
                            @{{ state.default_name }}
                        </option>
                    </x-shop::form.control-group.control>
                </template>

                <template v-else>
                    <x-shop::form.control-group.control type="text" name="billing.state" ::value="guest.cart.billingAddress.state"
                        {{-- rules="{{ core()->isStateRequired() ? 'required' : '' }}" --}}
                         :label="trans('shop::app.checkout.onepage.addresses.billing.state')"
                        :placeholder="trans('shop::app.checkout.onepage.addresses.billing.state')" />
                </template>

                <x-shop::form.control-group.error control-name="billing.state" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.state.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5">
            <!-- City -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.city')
                </x-shop::form.control-group.label>


                <template v-if="haveCities(values.billing?.state)">
                    <x-shop::form.control-group.control type="select" name="billing.city"
                        :label="trans('shop::app.checkout.onepage.addresses.billing.city')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.city')">
                        <option value="">
                            @lang('shop::app.checkout.onepage.addresses.billing.select-city')
                        </option>

                        <option v-for='(city, index) in cities[values.billing?.state]' :value="city.code">
                            @{{ city.default_name }}
                        </option>
                    </x-shop::form.control-group.control>
                </template>

                <template v-else>
                    <x-shop::form.control-group.control type="text" name="billing.city" ::value="guest.cart.billingAddress.city"
                        :label="trans('shop::app.checkout.onepage.addresses.billing.city')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.city')" />
                </template>

                {{-- <x-shop::form.control-group.control
                type="text"
                name="billing.city"
                ::value="guest.cart.billingAddress.city"
                rules="required"
                :label="trans('shop::app.checkout.onepage.addresses.billing.city')"
                :placeholder="trans('shop::app.checkout.onepage.addresses.billing.city')"
            /> --}}

                <x-shop::form.control-group.error control-name="billing.city" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.city.after') !!}

            <!-- Postcode -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="{{ core()->isPostCodeRequired() ? 'required' : '' }} !mt-0">
                    @lang('shop::app.checkout.onepage.addresses.billing.postcode')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.postcode" ::value="guest.cart.billingAddress.postcode"
                    {{-- rules="{{ core()->isPostCodeRequired() ? 'required' : '' }}" --}}
                     :label="trans('shop::app.checkout.onepage.addresses.billing.postcode')"
                    :placeholder="trans('shop::app.checkout.onepage.addresses.billing.postcode')" />

                <x-shop::form.control-group.error control-name="billing.postcode" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.postcode.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5" v-if="values.billing?.country == 'KW'">

            <!-- Block Address -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.block-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.address1[0]" ::value="guest.cart.billingAddress.address1 && guest.cart.billingAddress.address1.length > 0 ? guest.cart.billingAddress.address1[0] : ''"
                     :label="trans('shop::app.checkout.onepage.addresses.billing.block-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.block-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="billing.address1[0]" />

                {{-- @if (core()->getConfigData('customer.address.information.street_lines') > 1)
                    @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
                        <x-shop::form.control-group.control type="text"
                            name="billing.address1[{{ $i }}]" :label="trans('shop::app.checkout.onepage.addresses.billing.block-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.street-address')" />
                    @endfor
                @endif --}}
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.block.after') !!}

            <!-- street -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.street-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.address1[1]" ::value="guest.cart.billingAddress.address1 && guest.cart.billingAddress.address1.length > 1 ? guest.cart.billingAddress.address1[1] : ''"
                     :label="trans('shop::app.checkout.onepage.addresses.billing.street-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.street-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="billing.address1[1]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.street.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5" v-if="values.billing?.country == 'KW'">
             <!-- House -->
             <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.house-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.address1[2]" ::value="guest.cart.billingAddress.address1 && guest.cart.billingAddress.address1.length > 2 ? guest.cart.billingAddress.address1[2] : ''"
                     :label="trans('shop::app.checkout.onepage.addresses.billing.house-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.house-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="billing.address1[2]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.house.after') !!}

            <!-- Floor -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.floor-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.address1[3]" ::value="guest.cart.billingAddress.address1 && guest.cart.billingAddress.address1.length > 3 ? guest.cart.billingAddress.address1[3] : ''"
                    rules="address" :label="trans('shop::app.checkout.onepage.addresses.billing.floor-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.floor-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="billing.address1[3]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.floor.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5" v-if="values.billing?.country == 'KW'">
             <!-- Flat -->
             <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.flat-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.address1[4]" ::value="guest.cart.billingAddress.address1 && guest.cart.billingAddress.address1.length > 4 ? guest.cart.billingAddress.address1[4] : ''"
                    rules="address" :label="trans('shop::app.checkout.onepage.addresses.billing.flat-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.flat-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="billing.address1[4]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.flat.after') !!}

            <!-- Avenue -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.billing.avenue-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="billing.address1[5]" ::value="guest.cart.billingAddress.address1 && guest.cart.billingAddress.address1.length > 5 ? guest.cart.billingAddress.address1[5] : ''"
                    rules="address" :label="trans('shop::app.checkout.onepage.addresses.billing.avenue-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.avenue-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="billing.address1[5]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.avenue.after') !!}
        </div>

        <!-- Street Address -->
        <x-shop::form.control-group v-if="values.billing?.country != 'KW'">
            <x-shop::form.control-group.label class="!mt-0 required">
                @lang('shop::app.checkout.onepage.addresses.billing.street-address')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="text" ::value="guest.cart.billingAddress.address1 && guest.cart.billingAddress.address1.length > 0 ? guest.cart.billingAddress.address1[0] : ''" name="billing.address1[0]"
                 :label="trans('shop::app.checkout.onepage.addresses.billing.street-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.street-address')" />

            <x-shop::form.control-group.error class="mb-2" control-name="billing.address1[0]" />

            @if (core()->getConfigData('customer.address.information.street_lines') > 1)
                @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
                    <x-shop::form.control-group.control type="text" name="billing.address1[{{ $i }}]"
                        :label="trans('shop::app.checkout.onepage.addresses.billing.street-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.street-address')" />
                @endfor
            @endif
        </x-shop::form.control-group>

        <!-- Note Address -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="!mt-0">
                @lang('shop::app.checkout.onepage.addresses.billing.note-address')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="text" ::value="guest.cart.billingAddress.note" name="billing.note"
                rules="address" :label="trans('shop::app.checkout.onepage.addresses.billing.note-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.note-address')" />

            <x-shop::form.control-group.error class="mb-2" control-name="billing.note" />

        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.address1.after') !!}

        <!-- Phone Number -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="!mt-0 required">
                @lang('shop::app.checkout.onepage.addresses.billing.telephone')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="text" name="billing.phone" ::value="guest.cart.billingAddress.phone"
                 :label="trans('shop::app.checkout.onepage.addresses.billing.telephone')" :placeholder="trans('shop::app.checkout.onepage.addresses.billing.telephone')" />

            <x-shop::form.control-group.error control-name="billing.phone" />
        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.phone.after') !!}

        <!-- Checkbox For Different Address -->
        {{-- <div class="flex gap-x-1.5 items-center mt-5 text-sm text-[#6E6E6E] select-none">
            <div v-if="guest.applied.useDifferentAddressForShipping">
                <x-shop::form.control-group.control type="hidden" name="billing.use_different_address_for_shipping"
                    ::value="!!guest.applied.useDifferentAddressForShipping" />
            </div>

            <v-field type="checkbox" name="billing.use_different_address_for_shipping" v-slot="{ field }"
                :value="true">
                <input type="checkbox" name="billing.use_different_address_for_shipping"
                    id="billing.use_different_address_for_shipping" class="sr-only peer" v-bind="field"
                    :checked="!!guest.applied.useDifferentAddressForShipping"
                    @click="guest.applied.useDifferentAddressForShipping = ! guest.applied.useDifferentAddressForShipping;" />
            </v-field>

            <label
                class="icon-uncheck text-2xl text-navyBlue peer-checked:icon-check-box peer-checked:text-navyBlue cursor-pointer"
                for="billing.use_different_address_for_shipping">
            </label>

            <label for="billing.use_different_address_for_shipping" class="cursor-pointer">
                @lang('shop::app.checkout.onepage.addresses.billing.use-different-address-for-shipping')
            </label>
        </div> --}}

        <!-- Proceed Button -->
        <div class="flex justify-end mt-4" v-if="! guest.applied.useDifferentAddressForShipping">
            <x-shop::button class="primary-button py-3 px-11 rounded-2xl" :title="trans('shop::app.checkout.onepage.addresses.billing.proceed')" :loading="false"
                v-if="! isLoading" />

            <x-shop::button class="primary-button py-3 px-11 rounded-2xl" :title="trans('shop::app.checkout.onepage.addresses.billing.proceed')" :loading="true"
                :disabled="true" v-else />
        </div>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.billing.form.after') !!}
    </div>
</div>
