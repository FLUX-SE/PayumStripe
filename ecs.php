<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagNormalizerFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAliasTagFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ECSConfig): void {
    $ECSConfig->import('vendor/symplify/easy-coding-standard/config/set/psr12.php');

    $services = $ECSConfig->services();

    $services->set(BlankLineAfterOpeningTagFixer::class);

    // PhpDoc blocks
    $services->set(NoBlankLinesAfterPhpdocFixer::class);
    $services->set(NoEmptyPhpdocFixer::class);
    $services->set(PhpdocIndentFixer::class);
    $services->set(PhpdocInlineTagNormalizerFixer::class);
    $services->set(PhpdocNoAccessFixer::class);
    $services->set(PhpdocNoAliasTagFixer::class);
    $services->set(PhpdocNoEmptyReturnFixer::class);
    $services->set(PhpdocNoPackageFixer::class);
    $services->set(PhpdocNoUselessInheritdocFixer::class);
    $services->set(PhpdocReturnSelfReferenceFixer::class);
    $services->set(PhpdocScalarFixer::class);
    $services->set(PhpdocSeparationFixer::class);
    $services->set(PhpdocSingleLineVarSpacingFixer::class);
    $services->set(PhpdocTrimFixer::class);
    $services->set(PhpdocTypesFixer::class);
    $services->set(PhpdocVarWithoutNameFixer::class);
    $services->set(NoSuperfluousPhpdocTagsFixer::class)
        ->call('configure', [
            [
                'allow_mixed' => true,
            ],
        ]);
    $services->set(PhpdocTypesOrderFixer::class)
        ->call('configure', [
            [
                'null_adjustment' => 'always_last',
                'sort_algorithm' => 'none',
            ],
        ]);
};
