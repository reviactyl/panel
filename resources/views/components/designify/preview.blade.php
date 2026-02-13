<main class="flex-1 flex flex-col">
   <div class="flex items-center justify-between p-2 bg-zinc-800 rounded-lg rounded-b-none border border-zinc-700">
      <div class="flex items-center space-x-2">
         <span class="text-base text-white/50 bg-zinc-900 px-5 rounded-lg mr-2">
         Preview
         </span>
         @if(Route::currentRouteName() === 'admin.designify.errors')
            <div class="flex items-center space-x-1 bg-zinc-900 p-1 rounded-lg border border-zinc-700">
               <button onclick="changePreview('/')" class="error-preview-btn active px-3 py-1 rounded-md text-xs font-bold text-white/50 hover:text-white transition-all" data-code="default">Dashboard</button>
               <button onclick="changePreview('{{ route('admin.designify.errors.preview', 403) }}')" class="error-preview-btn px-3 py-1 rounded-md text-xs font-bold text-white/50 hover:text-white transition-all" data-code="403">403</button>
               <button onclick="changePreview('{{ route('admin.designify.errors.preview', 404) }}')" class="error-preview-btn px-3 py-1 rounded-md text-xs font-bold text-white/50 hover:text-white transition-all" data-code="404">404</button>
               <button onclick="changePreview('{{ route('admin.designify.errors.preview', 500) }}')" class="error-preview-btn px-3 py-1 rounded-md text-xs font-bold text-white/50 hover:text-white transition-all" data-code="500">500</button>
            </div>
         @endif
      </div>
      <div class="flex items-center space-x-1 bg-zinc-800 p-1 rounded-lg border border-zinc-700">
         <button id="preview-desktop" class="preview-btn active p-2 rounded-md text-white/50">
            <x-heroicon-m-computer-desktop class="h-4 w-4"/>
         </button>
         <button id="preview-tablet" class="preview-btn p-2 rounded-md text-white/50">
            <x-heroicon-m-device-phone-mobile class="h-4 w-4"/>
         </button>
      </div>
   </div>
   <div class="flex-1 flex items-center justify-center bg-zinc-800 border border-zinc-700 rounded-lg rounded-t-none">
      <div id="preview-container" class="transition-all duration-300 w-full h-full">
         <iframe id="designify-preview-iframe" name="designify-preview-iframe" src="/" class="w-full h-full shadow-2xl bg-white"></iframe>
      </div>
   </div>
</main>

<script>
   let lastPreviewUrl = '/';
   
   function changePreview(url) {
      const iframe = document.getElementById('designify-preview-iframe');
      const form = document.getElementById('designifyEditor');
      
      if (iframe) {
         lastPreviewUrl = url;
         
         if (form && url !== '/') {
            // Live preview: submit form to iframe
            const originalAction = form.action;
            const originalTarget = form.target;
            const originalMethod = form.method;
            
            // Temporary change action and target
            form.action = url;
            form.target = 'designify-preview-iframe';
            form.method = 'POST';
            
            form.submit();
            
            // Restore form attributes
            form.action = originalAction;
            form.target = originalTarget;
            form.method = originalMethod;
         } else {
            iframe.src = url;
         }
         
         // Update active state of buttons
         document.querySelectorAll('.error-preview-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-zinc-800', 'text-white');
            btn.classList.add('text-white/50');
            
            if (btn.getAttribute('onclick').includes(url)) {
               btn.classList.add('active', 'bg-zinc-800', 'text-white');
               btn.classList.remove('text-white/50');
            }
            if (url === '/' && btn.dataset.code === 'default') {
               btn.classList.add('active', 'bg-zinc-800', 'text-white');
               btn.classList.remove('text-white/50');
            }
         });
      }
   }

   // Function to refresh current preview with latest form data
   function refreshPreview() {
      if (lastPreviewUrl && lastPreviewUrl !== '/') {
         changePreview(lastPreviewUrl);
      }
   }
</script>