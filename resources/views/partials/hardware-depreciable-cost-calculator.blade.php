<script nonce="{{ csrf_token() }}">
    $(function () {
        const $purchaseCost = $("#purchase_cost");
        const $quality = $("#quality");
        const $newDepreciableCost = $("#new_depreciable_cost");
        const persistedPurchaseCost = Number(@json((float) ($asset->purchase_cost ?? 0)));

        if (!$purchaseCost.length || !$quality.length || !$newDepreciableCost.length) {
            return;
        }

        const qualityMultipliers = {
            5: 1,
            4: 0.8,
            3: 0.5,
            2: 0.3,
            1: 0,
        };

        const lifetime = Math.max(
            Number(@json((int) ($asset->model?->depreciation?->months ?? 36))),
            1
        );

        const purchaseDate = @json(optional($asset->purchase_date)->format('Y-m-d'));
        const defaultUseTime = 12;

        const calculateCost = function () {
            const purchaseCost = $purchaseCost.is(":disabled")
                ? persistedPurchaseCost
                : parseMoney($purchaseCost.val());
            const quality = Number.parseInt($quality.val(), 10);
            const qualityMultiplier = qualityMultipliers[quality];

            if (purchaseCost <= 0 || qualityMultiplier === undefined) {
                $newDepreciableCost.val("");
                return;
            }

            const useTime = purchaseDate
                ? Math.max(monthDiff(new Date(`${purchaseDate}T00:00:00`)), 0)
                : defaultUseTime;

            const depreciatedCost = purchaseCost - ((purchaseCost / lifetime) * useTime);
            const newValue = Math.max(depreciatedCost * qualityMultiplier, 0);

            $newDepreciableCost.val(Number.isFinite(newValue) ? newValue.toFixed(2) : "0.00");
        };

        $quality.add($purchaseCost).on("change input", calculateCost);

        calculateCost();

        function monthDiff(dateFrom) {
            const dateTo = new Date();

            return ((dateTo.getFullYear() - dateFrom.getFullYear()) * 12)
                + (dateTo.getMonth() - dateFrom.getMonth());
        }

        function parseMoney(value) {
            let normalized = String(value || "")
                .replace(/[\s']/g, "")
                .replace(/[^\d,.-]/g, "");
            const lastComma = normalized.lastIndexOf(",");
            const lastDot = normalized.lastIndexOf(".");

            if (lastComma !== -1 && lastDot !== -1) {
                const decimalSeparator = lastComma > lastDot ? "," : ".";
                const thousandsSeparator = decimalSeparator === "," ? "." : ",";

                normalized = normalized.split(thousandsSeparator).join("");
                normalized = normalized.replace(decimalSeparator, ".");
            } else if (lastComma !== -1) {
                normalized = normalized.replace(",", ".");
            }

            return Number.parseFloat(normalized) || 0;
        }
    });
</script>
