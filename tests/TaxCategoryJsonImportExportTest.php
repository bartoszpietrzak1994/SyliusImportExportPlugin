<?php

declare(strict_types=1);

namespace Tests\FriendsOfSylius\SyliusImportExportPlugin;

final class TaxCategoryJsonImportExportTest extends AbstractJsonImportExportTest
{
    protected function provideName(): string
    {
        return 'tax_category';
    }

    protected function provideJsonData(): string
    {
        return <<<LOL
[
    {
        "Code": "clothing",
        "Name": "Clothing",
        "Description": "Corrupti dolorem ut qui et voluptatem. Repellendus sint omnis exercitationem ut. Quas soluta omnis quae tenetur consequatur voluptate."
    },
    {
        "Code": "books",
        "Name": "Books",
        "Description": "Voluptatem quia fugiat quidem quae ut molestiae autem. Nostrum aut non sit voluptatem quae. Eum et ea rerum voluptas est doloribus accusamus."
    },
    {
        "Code": "other",
        "Name": "Other",
        "Description": "Eius consequuntur nihil asperiores corrupti perspiciatis aperiam praesentium. Deleniti nam sequi et numquam numquam. Repudiandae ut cupiditate tenetur eveniet."
    }
]
LOL;
    }
}
