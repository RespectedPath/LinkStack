        <!-- Short Bio -->
        <style>.description-parent * {margin-bottom: 1em;}.description-parent {padding-bottom: 30px;}</style>
        {{-- The description is a plain-text tagline: always escaped, and
             strip_tags() defends against any legacy HTML left in old rows so
             it never renders markup (and never nests <p> inside this <p>). --}}
        <center><div class="fadein description-parent dynamic-contrast"><p class="fadein">{{ strip_tags($info->littlelink_description ?? '') }}</p></div></center>