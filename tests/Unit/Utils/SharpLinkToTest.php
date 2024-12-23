<?php

namespace Code16\Sharp\Tests\Unit\Utils;

use Code16\Sharp\Exceptions\SharpInvalidBreadcrumbItemException;
use Code16\Sharp\Tests\SharpTestCase;
use Code16\Sharp\Utils\Filters\SelectFilter;
use Code16\Sharp\Utils\Links\BreadcrumbBuilder;
use Code16\Sharp\Utils\Links\LinkToEntityList;
use Code16\Sharp\Utils\Links\LinkToForm;
use Code16\Sharp\Utils\Links\LinkToShowPage;
use Code16\Sharp\Utils\Links\LinkToSingleForm;
use Code16\Sharp\Utils\Links\LinkToSingleShowPage;

class SharpLinkToTest extends SharpTestCase
{
    /** @test */
    public function we_can_generate_a_link_to_an_entity_list()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity" title="">test</a>',
            LinkToEntityList::make('my-entity')
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_an_entity_form()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity/s-form/my-entity/23" title="">test</a>',
            LinkToForm::make('my-entity', 23)
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_an_entity_form_through_a_show_page()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity/s-show/my-entity/23/s-form/my-entity/23" title="">test</a>',
            LinkToForm::make('my-entity', 23)
                ->throughShowPage()
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_a_show_page()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity/s-show/my-entity/23" title="">test</a>',
            LinkToShowPage::make('my-entity', 23)
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_a_single_show_page()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-show/my-entity" title="">test</a>',
            LinkToSingleShowPage::make('my-entity')
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_an_url_to_a_show_page_with_a_specific_breadcrumb()
    {
        $this->assertEquals(
            'http://localhost/sharp/s-list/base-entity/s-show/base-entity/3/s-show/my-entity/4',
            LinkToShowPage::make('my-entity', 4)
                ->withBreadcrumb(function (BreadcrumbBuilder $builder) {
                    return $builder
                        ->appendEntityList('base-entity')
                        ->appendShowPage('base-entity', 3);
                })
                ->renderAsUrl(),
        );
    }

    /** @test */
    public function we_can_generate_an_url_to_a_form_with_a_specific_breadcrumb()
    {
        $this->assertEquals(
            'http://localhost/sharp/s-list/base-entity/s-show/base-entity/3/s-show/my-entity/4/s-form/my-entity/4',
            LinkToForm::make('my-entity', 4)
                ->withBreadcrumb(function (BreadcrumbBuilder $builder) {
                    return $builder
                        ->appendEntityList('base-entity')
                        ->appendShowPage('base-entity', 3);
                })
                ->throughShowPage()
                ->renderAsUrl(),
        );
    }

    /** @test */
    public function we_can_generate_an_url_to_a_show_page_with_a_specific_breadcrumb_starting_with_a_single_show_page()
    {
        $this->assertEquals(
            'http://localhost/sharp/s-show/base-entity/s-show/my-entity/4',
            LinkToShowPage::make('my-entity', 4)
                ->withBreadcrumb(function (BreadcrumbBuilder $builder) {
                    return $builder->appendSingleShowPage('base-entity');
                })
                ->renderAsUrl(),
        );
    }

    /** @test */
    public function we_can_not_generate_an_url_with_a_specific_breadcrumb_starting_with_a_show()
    {
        $this->expectException(SharpInvalidBreadcrumbItemException::class);

        LinkToShowPage::make('my-entity', 4)
            ->withBreadcrumb(function (BreadcrumbBuilder $builder) {
                return $builder->appendShowPage('base-entity', 3);
            })
            ->renderAsUrl();
    }

    /** @test */
    public function we_can_not_push_a_entity_list_anywhere_else_than_in_the_first_spot()
    {
        $this->expectException(SharpInvalidBreadcrumbItemException::class);

        LinkToShowPage::make('my-entity', 4)
            ->withBreadcrumb(function (BreadcrumbBuilder $builder) {
                return $builder->appendShowPage('base-entity', 3)
                    ->appendEntityList('base-entity');
            })
            ->renderAsUrl();
    }

    /** @test */
    public function we_can_generate_a_link_to_an_entity_single_form()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-show/my-entity/s-form/my-entity" title="">test</a>',
            LinkToSingleForm::make('my-entity')
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_an_entity_list_with_a_search()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity?search=my-search" title="">test</a>',
            LinkToEntityList::make('my-entity')
                ->setSearch('my-search')
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_an_entity_list_with_a_filter()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity?filter_country=France&filter_city=Paris" title="">test</a>',
            LinkToEntityList::make('my-entity')
                ->addFilter('country', 'France')
                ->addFilter('city', 'Paris')
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_an_entity_list_with_a_filter_classname()
    {
        $filter = new class() extends SelectFilter
        {
            public function buildFilterConfig(): void
            {
                $this->configureKey('my-key');
            }

            public function values(): array
            {
                return [
                    1 => 'one',
                    2 => 'two',
                ];
            }
        };

        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity?filter_my-key=1" title="">test</a>',
            LinkToEntityList::make('my-entity')
                ->addFilter($filter::class, 1)
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_to_an_entity_list_with_a_sort()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity?sort=name&dir=desc" title="">test</a>',
            LinkToEntityList::make('my-entity')
                ->setSort('name', 'desc')
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_a_link_with_a_tooltip()
    {
        $this->assertEquals(
            '<a href="http://localhost/sharp/s-list/my-entity" title="tooltip">test</a>',
            LinkToEntityList::make('my-entity')
                ->setTooltip('tooltip')
                ->renderAsText('test'),
        );
    }

    /** @test */
    public function we_can_generate_an_url()
    {
        $this->assertEquals(
            'http://localhost/sharp/s-list/my-entity',
            LinkToEntityList::make('my-entity')
                ->renderAsUrl(),
        );
    }
}
