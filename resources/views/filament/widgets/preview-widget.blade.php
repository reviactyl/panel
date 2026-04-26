<x-filament::widget>
    <x-filament::card>
        <div 
            x-data 
            x-on:reload-iframe.window="
                const iframe = document.getElementById('preview-frame');
                if (iframe) {
                    iframe.src = iframe.src.split('?')[0] + '?t=' + Date.now();
                }
            "
            style="width:100%; height:80vh; overflow:hidden; border-radius:12px;"
        >
            <iframe 
                id="preview-frame" 
                src="{{ url('/') }}" 
                style="width:100%; height:100%; border:0;"
            ></iframe>
        </div>
    </x-filament::card>
</x-filament::widget>
