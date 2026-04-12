<?php

namespace MalikAd778\BladeAlly\Violations;

class ViolationDiff
{
    public function diff(ViolationCollection $current, array $baseline): ViolationCollection
    {
        $baselineFingerprints = [];
        foreach ($baseline as $entry) {
            $fp = is_array($entry) ? ($entry['fingerprint'] ?? null) : ($entry->fingerprint ?? null);
            if ($fp) {
                $baselineFingerprints[] = $fp;
            }
        }

        $new = new ViolationCollection();
        foreach ($current->all() as $violation) {
            if (!in_array($violation->fingerprint(), $baselineFingerprints, true)) {
                $new->add($violation);
            }
        }

        return $new;
    }
}
