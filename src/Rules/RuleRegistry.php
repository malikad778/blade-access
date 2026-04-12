<?php

namespace MalikAd778\BladeAlly\Rules;

use MalikAd778\BladeAlly\Rules\Contracts\RuleInterface;
use MalikAd778\BladeAlly\Rules\Images\ImgMissingAltRule;
use MalikAd778\BladeAlly\Rules\Images\ImgEmptyAltOnMeaningfulRule;
use MalikAd778\BladeAlly\Rules\Images\ImgAltRedundantRule;
use MalikAd778\BladeAlly\Rules\Images\ImgAltFilenameRule;
use MalikAd778\BladeAlly\Rules\Images\ImgAltTooLongRule;
use MalikAd778\BladeAlly\Rules\Images\SvgMissingTitleRule;
use MalikAd778\BladeAlly\Rules\Images\SvgRoleImgRule;
use MalikAd778\BladeAlly\Rules\Images\IconButtonLabelRule;
use MalikAd778\BladeAlly\Rules\Images\BackgroundImageContentRule;
use MalikAd778\BladeAlly\Rules\Images\InlineColorNoBgRule;
use MalikAd778\BladeAlly\Rules\Images\HardcodedColorValueRule;
use MalikAd778\BladeAlly\Rules\Images\VideoMissingCaptionsRule;
use MalikAd778\BladeAlly\Rules\Images\VideoAutoplayNoControlsRule;
use MalikAd778\BladeAlly\Rules\Images\AudioMissingTranscriptRule;
use MalikAd778\BladeAlly\Rules\Forms\InputMissingLabelRule;
use MalikAd778\BladeAlly\Rules\Forms\InputPlaceholderAsLabelRule;
use MalikAd778\BladeAlly\Rules\Forms\SelectMissingLabelRule;
use MalikAd778\BladeAlly\Rules\Forms\TextareaMissingLabelRule;
use MalikAd778\BladeAlly\Rules\Forms\FieldsetMissingLegendRule;
use MalikAd778\BladeAlly\Rules\Forms\FormErrorMissingSuggestionRule;
use MalikAd778\BladeAlly\Rules\Forms\RequiredNotIndicatedRule;
use MalikAd778\BladeAlly\Rules\Forms\InputTypeDateAccessibleRule;
use MalikAd778\BladeAlly\Rules\Forms\AutocompleteAttributeRule;
use MalikAd778\BladeAlly\Rules\Forms\LabelOrphanedRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\ButtonEmptyRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\ButtonGenericLabelRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\LinkEmptyRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\LinkGenericLabelRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\LinkOpensNewTabNoWarningRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\LinkHrefJavascriptRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\ButtonRoleOnDivRule;
use MalikAd778\BladeAlly\Rules\ButtonsLinks\LinkToAnchorExistsRule;
use MalikAd778\BladeAlly\Rules\Headings\HeadingEmptyRule;
use MalikAd778\BladeAlly\Rules\Headings\HeadingLogicalOrderRule;
use MalikAd778\BladeAlly\Rules\Headings\H1PresenceRule;
use MalikAd778\BladeAlly\Rules\Headings\HeadingUsedForStylingRule;
use MalikAd778\BladeAlly\Rules\Headings\HeadingMultipleH1Rule;
use MalikAd778\BladeAlly\Rules\Tables\TableCaptionRule;
use MalikAd778\BladeAlly\Rules\Tables\TableThScopeRule;
use MalikAd778\BladeAlly\Rules\Tables\TableLayoutRolePresentationRule;
use MalikAd778\BladeAlly\Rules\Dialogs\DialogLabelRule;
use MalikAd778\BladeAlly\Rules\Dialogs\DialogAriaModalRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaRolesRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaHiddenFocusableRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaRequiredChildrenRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaRequiredParentRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaLabelledbyTargetMissingRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaDescribedbyTargetMissingRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaExpandedNoControlRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaLiveRegionStaticRule;
use MalikAd778\BladeAlly\Rules\Aria\AriaLabelMatchesTextRule;
use MalikAd778\BladeAlly\Rules\Structure\HtmlLangValidRule;
use MalikAd778\BladeAlly\Rules\Structure\HtmlLangMissingRule;
use MalikAd778\BladeAlly\Rules\Structure\TabindexNoPositiveRule;
use MalikAd778\BladeAlly\Rules\Structure\TabindexMissingOnInteractiveRule;
use MalikAd778\BladeAlly\Rules\Structure\IframeTitleRule;
use MalikAd778\BladeAlly\Rules\Structure\DocumentTitleRule;
use MalikAd778\BladeAlly\Rules\Structure\MetaViewportScaleRule;
use MalikAd778\BladeAlly\Rules\Structure\LandmarkOneMainRule;
use MalikAd778\BladeAlly\Rules\Structure\LandmarkMissingNavRule;
use MalikAd778\BladeAlly\Rules\Structure\LandmarkDuplicateBannerRule;
use MalikAd778\BladeAlly\Rules\Structure\LandmarkDuplicateMainRule;
use MalikAd778\BladeAlly\Rules\Structure\ListNotSemanticRule;
use MalikAd778\BladeAlly\Rules\Structure\SkipLinkMissingRule;
use MalikAd778\BladeAlly\Rules\Structure\SkipLinkTargetMissingRule;
use MalikAd778\BladeAlly\Rules\Structure\FocusVisibleSuppressedRule;
use MalikAd778\BladeAlly\Rules\Structure\AccesskeyRule;
use MalikAd778\BladeAlly\Rules\Livewire\WireClickKeyboardRule;
use MalikAd778\BladeAlly\Rules\Livewire\WireLoadingAriaLiveRule;
use MalikAd778\BladeAlly\Rules\Livewire\WireNavigateLinkRule;
use MalikAd778\BladeAlly\Rules\Livewire\WireTargetMatchRule;
use MalikAd778\BladeAlly\Rules\Livewire\LivewirePollNoPauseRule;
use MalikAd778\BladeAlly\Rules\Livewire\LivewireDispatchFocusRule;

class RuleRegistry
{
    private array $rules  = [];
    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->registerDefaultRules();
        $this->applyConfigOverrides();
    }

    public function registerDefaultRules(): void
    {
        $defaults = [
            new ImgMissingAltRule(), new ImgEmptyAltOnMeaningfulRule(), new ImgAltRedundantRule(),
            new ImgAltFilenameRule(), new ImgAltTooLongRule(), new SvgMissingTitleRule(),
            new SvgRoleImgRule(), new IconButtonLabelRule(), new BackgroundImageContentRule(),
            new InlineColorNoBgRule(), new HardcodedColorValueRule(),
            new VideoMissingCaptionsRule(), new VideoAutoplayNoControlsRule(), new AudioMissingTranscriptRule(),
            new InputMissingLabelRule(), new InputPlaceholderAsLabelRule(), new SelectMissingLabelRule(),
            new TextareaMissingLabelRule(), new FieldsetMissingLegendRule(), new FormErrorMissingSuggestionRule(),
            new RequiredNotIndicatedRule(), new InputTypeDateAccessibleRule(), new AutocompleteAttributeRule(),
            new LabelOrphanedRule(),
            new ButtonEmptyRule(), new ButtonGenericLabelRule(), new LinkEmptyRule(),
            new LinkGenericLabelRule(), new LinkOpensNewTabNoWarningRule(), new LinkHrefJavascriptRule(),
            new ButtonRoleOnDivRule(), new LinkToAnchorExistsRule(),
            new HeadingEmptyRule(), new HeadingLogicalOrderRule(), new H1PresenceRule(),
            new HeadingUsedForStylingRule(), new HeadingMultipleH1Rule(),
            new TableCaptionRule(), new TableThScopeRule(), new TableLayoutRolePresentationRule(),
            new DialogLabelRule(), new DialogAriaModalRule(),
            new AriaRolesRule(), new AriaHiddenFocusableRule(), new AriaRequiredChildrenRule(),
            new AriaRequiredParentRule(), new AriaLabelledbyTargetMissingRule(),
            new AriaDescribedbyTargetMissingRule(), new AriaExpandedNoControlRule(),
            new AriaLiveRegionStaticRule(), new AriaLabelMatchesTextRule(),
            new HtmlLangValidRule(), new HtmlLangMissingRule(), new TabindexNoPositiveRule(),
            new TabindexMissingOnInteractiveRule(), new IframeTitleRule(), new DocumentTitleRule(),
            new MetaViewportScaleRule(), new LandmarkOneMainRule(), new LandmarkMissingNavRule(),
            new LandmarkDuplicateBannerRule(), new LandmarkDuplicateMainRule(),
            new ListNotSemanticRule(), new SkipLinkMissingRule(), new SkipLinkTargetMissingRule(),
            new FocusVisibleSuppressedRule(), new AccesskeyRule(),
            new WireClickKeyboardRule(), new WireLoadingAriaLiveRule(), new WireNavigateLinkRule(),
            new WireTargetMatchRule(), new LivewirePollNoPauseRule(), new LivewireDispatchFocusRule(),
        ];

        foreach ($defaults as $rule) {
            $this->rules[$rule->getId()] = $rule;
        }
    }

    private function applyConfigOverrides(): void
    {
        $rulesConfig = $this->config['rules'] ?? [];

        foreach ($rulesConfig as $id => $setting) {
            if ($setting === false) {
                unset($this->rules[$id]);
            }
        }
    }

    public function add(RuleInterface $rule): self
    {
        $this->rules[$rule->getId()] = $rule;
        return $this;
    }

    public function remove(string $id): self
    {
        unset($this->rules[$id]);
        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function all(): array
    {
        return $this->rules;
    }

    public function getByCategory(string $category): array
    {
        return array_filter($this->rules, fn (RuleInterface $r) => $r->getCategory() === $category);
    }
}
