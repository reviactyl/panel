<aside class="w-80 mr-4 flex flex-col text-white">
   <div class="flex items-center space-x-1 bg-zinc-800 p-1 rounded-lg border border-zinc-700">
      <x-designify.tab-button
         :route="route('admin.designify.general')"
         icon="fa-solid fa-bolt"
         label="General Options"
         :active="Route::currentRouteName() === 'admin.designify.general'" />
      <x-designify.tab-button
         :route="route('admin.designify.colors')"
         icon="fa-solid fa-palette"
         label="Color Options"
         :active="str_starts_with(Route::currentRouteName(),'admin.designify.colors')" />
      <x-designify.tab-button
         :route="route('admin.designify.looks')"
         icon="fa-solid fa-swatchbook"
         label="Look 'N Feel"
         :active="str_starts_with(Route::currentRouteName(),'admin.designify.looks')" />
      <x-designify.tab-button
         :route="route('admin.designify.alerts')"
         icon="fa-solid fa-bullhorn"
         label="Alerts"
         :active="str_starts_with(Route::currentRouteName(),'admin.designify.alerts')" />
      <x-designify.tab-button
         :route="route('admin.designify.site')"
         icon="fa-solid fa-gear"
         label="Site Meta Settings"
         :active="str_starts_with(Route::currentRouteName(),'admin.designify.site')" />
      <x-designify.tab-button
         :route="route('admin.designify.errors')"
         icon="fa-solid fa-triangle-exclamation"
         label="Error Pages"
         :active="str_starts_with(Route::currentRouteName(),'admin.designify.errors')" />
      @include('partials.admin.designify.save')
   </div>
   <div class="flex-1 overflow-y-auto mt-2 pr-1 text-white p-1">
      @if($errors->any())
      <div class="mb-4 bg-blue-900/20 border border-blue-800 text-blue-300 px-4 py-3 rounded-xl">
         <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
         </ul>
      </div>
      @endif
      {{ $slot }}
   </div>
</aside>