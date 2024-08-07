<div class="mt-4" v-if="cart.have_stockable_items && guest.applied.useDifferentAddressForShipping">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-medium max-sm:text-xl">
            @lang('shop::app.checkout.onepage.addresses.shipping.shipping-address')
        </h2>
    </div>

    <div class="mt-2">
        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.form.before') !!}

        <!-- Company Name -->
        {{-- <x-shop::form.control-group>
            <x-shop::form.control-group.label>
                @lang('shop::app.checkout.onepage.addresses.shipping.company-name')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control
                type="text"
                name="shipping.company_name"
                ::value="guest.cart.shippingAddress.companyName"
                :label="trans('shop::app.checkout.onepage.addresses.shipping.company-name')"
                :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.company-name')"
            />

            <x-shop::form.control-group.error control-name="shipping.company_name" />
        </x-shop::form.control-group> --}}

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.company_name.after') !!}

        <div class="grid grid-cols-2 gap-x-5">
            <!-- First Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.shipping.first-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.first_name" ::value="guest.cart.shippingAddress.firstName"
                    :label="trans('shop::app.checkout.onepage.addresses.shipping.first-name')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.first-name')" />

                <x-shop::form.control-group.error control-name="shipping.first_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.first_name.after') !!}

            <!-- Last Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.shipping.last-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.last_name" ::value="guest.cart.shippingAddress.lastName"
                    :label="trans('shop::app.checkout.onepage.addresses.shipping.last-name')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.last-name')" />

                <x-shop::form.control-group.error control-name="shipping.last_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.last_name.after') !!}
        </div>

        <!-- Email -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="!mt-0 required">
                @lang('shop::app.checkout.onepage.addresses.shipping.email')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="email" name="shipping.email" ::value="guest.cart.shippingAddress.email"
                {{-- rules="required|email"  --}}
                :label="trans('shop::app.checkout.onepage.addresses.shipping.email')" placeholder="email@example.com" />

            <x-shop::form.control-group.error control-name="shipping.email" />
        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.email.after') !!}

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.address1.after') !!}

        <div class="grid grid-cols-2 gap-x-5">
            <!-- Country -->
            <x-shop::form.control-group class="!mb-4">
                <x-shop::form.control-group.label class="{{ core()->isCountryRequired() ? 'required' : '' }} !mt-0">
                    @lang('shop::app.checkout.onepage.addresses.shipping.country')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="select" name="shipping.country" ::value="guest.cart.shippingAddress.country"
                    rules="{{ core()->isCountryRequired() ? 'required' : '' }}" :label="trans('shop::app.checkout.onepage.addresses.shipping.country')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.country')">
                    <option value="">
                        @lang('shop::app.checkout.onepage.addresses.shipping.select-country')
                    </option>

                    <option v-for="country in countries" :value="country.code" v-text="country.name">
                    </option>
                </x-shop::form.control-group.control>

                <x-shop::form.control-group.error control-name="shipping.country" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.country.after') !!}

            <!-- State -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="{{ core()->isStateRequired() ? 'required' : '' }} !mt-0">
                    @lang('shop::app.checkout.onepage.addresses.shipping.state')
                </x-shop::form.control-group.label>

                <template v-if="haveStates(values.shipping?.country)">
                    <x-shop::form.control-group.control type="select" name="shipping.state"
                        rules="{{ core()->isStateRequired() ? 'required' : '' }}" :label="trans('shop::app.checkout.onepage.addresses.shipping.state')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.state')">
                        <option value="">
                            @lang('shop::app.checkout.onepage.addresses.shipping.select-state')
                        </option>

                        <option v-for='(state, index) in states[values.shipping?.country]' :value="state.code">
                            @{{ state.default_name }}
                        </option>
                    </x-shop::form.control-group.control>
                </template>

                <template v-else>
                    <x-shop::form.control-group.control type="text" name="shipping.state" ::value="guest.cart.shippingAddress.state"
                        rules="{{ core()->isStateRequired() ? 'required' : '' }}" :label="trans('shop::app.checkout.onepage.addresses.shipping.state')"
                        :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.state')" />
                </template>

                <x-shop::form.control-group.error control-name="shipping.state" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.state.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5">
            <!-- City -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.shipping.city')
                </x-shop::form.control-group.label>

                <template v-if="haveCities(values.shipping?.state)">
                    <x-shop::form.control-group.control type="select" name="shipping.city"
                        :label="trans('shop::app.checkout.onepage.addresses.shipping.city')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.city')">
                        <option value="">
                            @lang('shop::app.checkout.onepage.addresses.shipping.select-city')
                        </option>

                        <option v-for='(city, index) in cities[values.shipping?.state]' :value="city.code">
                            @{{ city.default_name }}
                        </option>
                    </x-shop::form.control-group.control>
                </template>

                <template v-else>
                    <x-shop::form.control-group.control type="text" name="shipping.city" ::value="guest.cart.shippingAddress.city"
                        :label="trans('shop::app.checkout.onepage.addresses.shipping.city')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.city')" />
                </template>

                {{-- <x-shop::form.control-group.control
                    type="text"
                    name="shipping.city"
                    ::value="guest.cart.shippingAddress.city"

                    :label="trans('shop::app.checkout.onepage.addresses.shipping.city')"
                    :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.city')"
                /> --}}

                <x-shop::form.control-group.error control-name="shipping.city" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.city.after') !!}

            <!-- Postcode -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="{{ core()->isPostCodeRequired() ? 'required' : '' }} !mt-0">
                    @lang('shop::app.checkout.onepage.addresses.shipping.postcode')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.postcode" ::value="guest.cart.shippingAddress.postcode"
                    rules="{{ core()->isPostCodeRequired() ? 'required' : '' }}" :label="trans('shop::app.checkout.onepage.addresses.shipping.postcode')"
                    :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.postcode')" />

                <x-shop::form.control-group.error control-name="shipping.postcode" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.postcode.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5" v-if="values.shipping?.country == 'KW'">

            <!-- Block Address -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.shipping.block-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.address1[0]" ::value="guest.cart.shippingAddress.address1 && guest.cart.shippingAddress.address1.length > 0 ? guest.cart
                    .shippingAddress.address1[0] : ''"
                     :label="trans('shop::app.checkout.onepage.addresses.shipping.block-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.block-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="shipping.address1[0]" />

                {{-- @if (core()->getConfigData('customer.address.information.street_lines') > 1)
                    @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
                        <x-shop::form.control-group.control type="text"
                            name="shipping.address1[{{ $i }}]" :label="trans('shop::app.checkout.onepage.addresses.shipping.block-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.street-address')" />
                    @endfor
                @endif --}}
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.block.after') !!}

            <!-- street -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.shipping.street-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.address1[1]" ::value="guest.cart.shippingAddress.address1 && guest.cart.shippingAddress.address1.length > 1 ? guest.cart
                    .shippingAddress.address1[1] : ''"
                     :label="trans('shop::app.checkout.onepage.addresses.shipping.street-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.street-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="shipping.address1[1]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.street.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5" v-if="values.shipping?.country == 'KW'">
            <!-- House -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0 required">
                    @lang('shop::app.checkout.onepage.addresses.shipping.house-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.address1[2]" ::value="guest.cart.shippingAddress.address1 && guest.cart.shippingAddress.address1.length > 2 ? guest.cart
                    .shippingAddress.address1[2] : ''"
                     :label="trans('shop::app.checkout.onepage.addresses.shipping.house-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.house-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="shipping.address1[2]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.house.after') !!}

            <!-- Floor -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0">
                    @lang('shop::app.checkout.onepage.addresses.shipping.floor-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.address1[3]" ::value="guest.cart.shippingAddress.address1 && guest.cart.shippingAddress.address1.length > 3 ? guest.cart
                    .shippingAddress.address1[3] : ''"
                    rules="address" :label="trans('shop::app.checkout.onepage.addresses.shipping.floor-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.floor-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="shipping.address1[3]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.floor.after') !!}
        </div>

        <div class="grid grid-cols-2 gap-x-5" v-if="values.shipping?.country == 'KW'">
            <!-- Flat -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0">
                    @lang('shop::app.checkout.onepage.addresses.shipping.flat-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.address1[4]" ::value="guest.cart.shippingAddress.address1 && guest.cart.shippingAddress.address1.length > 4 ? guest.cart
                    .shippingAddress.address1[4] : ''"
                    rules="address" :label="trans('shop::app.checkout.onepage.addresses.shipping.flat-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.flat-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="shipping.address1[4]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.flat.after') !!}

            <!-- Avenue -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="!mt-0">
                    @lang('shop::app.checkout.onepage.addresses.shipping.avenue-address')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control type="text" name="shipping.address1[5]" ::value="guest.cart.shippingAddress.address1 && guest.cart.shippingAddress.address1.length > 5 ? guest.cart
                    .shippingAddress.address1[5] : ''"
                    rules="address" :label="trans('shop::app.checkout.onepage.addresses.shipping.avenue-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.avenue-address')" />

                <x-shop::form.control-group.error class="mb-2" control-name="shipping.address1[5]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.avenue.after') !!}
        </div>

        <!-- Street Address -->
        <x-shop::form.control-group v-if="values.shipping?.country != 'KW'">
            <x-shop::form.control-group.label class="!mt-0 required">
                @lang('shop::app.checkout.onepage.addresses.shipping.street-address')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="text" name="shipping.address1[0]" ::value="guest.cart.shippingAddress.address1 && guest.cart.shippingAddress.address1.length > 0 ? guest.cart
                .shippingAddress.address1[0] : ''"
                 :label="trans('shop::app.checkout.onepage.addresses.shipping.street-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.street-address')" />

            <x-shop::form.control-group.error class="mb-2" control-name="shipping.address1[0]" />

            @if (core()->getConfigData('customer.address.information.street_lines') > 1)
                @for ($i = 1; $i < core()->getConfigData('customer.address.information.street_lines'); $i++)
                    <x-shop::form.control-group.control type="text" name="shipping.address1[{{ $i }}]"
                        :label="trans('shop::app.checkout.onepage.addresses.shipping.street-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.street-address')" />
                @endfor
            @endif
        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.address1.after') !!}

        <!-- Note Address -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="!mt-0">
                @lang('shop::app.checkout.onepage.addresses.shipping.note-address')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="text" ::value="guest.cart.shippingAddress.note" name="shipping.note"
                rules="address" :label="trans('shop::app.checkout.onepage.addresses.shipping.note-address')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.note-address')" />

            <x-shop::form.control-group.error class="mb-2" control-name="shipping.note" />

        </x-shop::form.control-group>

        <!-- Phone Number -->
        <x-shop::form.control-group>
            <x-shop::form.control-group.label class="!mt-0 required">
                @lang('shop::app.checkout.onepage.addresses.shipping.telephone')
            </x-shop::form.control-group.label>

            <x-shop::form.control-group.control type="text" name="shipping.phone" ::value="guest.cart.shippingAddress.phone"
                :label="trans('shop::app.checkout.onepage.addresses.shipping.telephone')" :placeholder="trans('shop::app.checkout.onepage.addresses.shipping.telephone')" />

            <x-shop::form.control-group.error control-name="shipping.phone" />
        </x-shop::form.control-group>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.phone.after') !!}

        <!-- Proceed Button -->
        <div class="flex justify-end mt-4">
            <x-shop::button class="primary-button py-3 px-11 rounded-2xl" :title="trans('shop::app.checkout.onepage.addresses.shipping.proceed')" :loading="false"
                v-if="! isLoading" />

            <x-shop::button class="primary-button py-3 px-11 rounded-2xl" :title="trans('shop::app.checkout.onepage.addresses.shipping.proceed')" :loading="true"
                :disabled="true" v-else />
        </div>

        {!! view_render_event('bagisto.shop.checkout.onepage.addresses.guest.shipping.form.after') !!}
    </div>
</div>
