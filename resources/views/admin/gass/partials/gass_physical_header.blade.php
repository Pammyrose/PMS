<div class="year-header">
    <div class="d-flex align-items-center justify-content-between" style="padding: 0 20px;">
        <span>(GASS) - Physical Performance</span>
        <span style="font-size: 1.2rem; font-weight: 600; color: #1e40af;">Year: <span id="headerYear" style="color: #dc2626;">{{ $year ?? now()->year }}</span></span>
        <span style="font-size: 0.95rem; color: #475569;">Total PAPs: <span id="headerPapCount" style="font-weight: 600;">{{ count($programs ?? []) }}</span></span>
    </div>
</div>
