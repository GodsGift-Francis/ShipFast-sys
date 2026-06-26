<footer class="bg-primary text-primary-foreground">
    <div class="container mx-auto px-4 py-14">
        <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
            <div>
                @include('partials.brand')
                <p class="mt-4 max-w-xs text-sm text-primary-foreground/60">Freight and parcel logistics across 190+ countries, with live tracking and proactive status alerts.</p>
            </div>
            <div>
                <p class="font-mono text-[11px] font-bold uppercase tracking-wider text-accent">Company</p>
                <ul class="mt-4 space-y-2 text-sm text-primary-foreground/70">
                    <li><a href="#" class="hover:text-primary-foreground">About</a></li>
                    <li><a href="#" class="hover:text-primary-foreground">Network</a></li>
                </ul>
            </div>
            <div>
                <p class="font-mono text-[11px] font-bold uppercase tracking-wider text-accent">Services</p>
                <ul class="mt-4 space-y-2 text-sm text-primary-foreground/70">
                    <li><a href="#" class="hover:text-primary-foreground">Express</a></li>
                    <li><a href="#" class="hover:text-primary-foreground">Freight</a></li>
                </ul>
            </div>
            <div>
                <p class="font-mono text-[11px] font-bold uppercase tracking-wider text-accent">Support</p>
                <ul class="mt-4 space-y-2 text-sm text-primary-foreground/70">
                    <li><a href="#" class="hover:text-primary-foreground">Track</a></li>
                    <li><a href="#" class="hover:text-primary-foreground">Get a quote</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 border-t border-primary-foreground/15 pt-6 font-mono text-[11px] uppercase tracking-wider text-primary-foreground/50">
            &copy; {{ date('Y') }} ShipFast. All rights reserved.
        </div>
    </div>
</footer>
