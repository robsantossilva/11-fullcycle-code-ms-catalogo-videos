<?php

namespace Tests\Unit\Rules;

use App\Rules\GenresHasCategoriesRule;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class GenresHasCategoriesRuleUnitTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new GenresHasCategoriesRule(
            [1,1,2,2]
        );
        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);

        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $categoriesId);
    }

    public function testGenresIdField()
    {
        /**
         * @var MockInterface $rule
         */
        $rule = $this->createRuleMock([]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturnNull();
        
        /**
         * @var GenresHasCategoriesRule $rule
         */
        $rule->passes('',[1,1,2,2]);

        $reflectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('genresId');
        $reflectionProperty->setAccessible(true);

        $genresId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1,2], $genresId);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresIsArrayEmpty()
    {
        /**
         * @var GenresHasCategoriesRule $rule
         */
        $rule = $this->createRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        /**
         * @var GenresHasCategoriesRule $rule
         */
        $rule = $this->createRuleMock([]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetRowIsEmpty()
    {
        $rule = $this->createRuleMock([1]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect());
        /**
         * @var GenresHasCategoriesRule $rule
         */
        $this->assertFalse($rule->passes('',[1]));
    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGenres()
    {
        $rule = $this->createRuleMock([1,2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([['category_id'=>1]]));
        /**
         * @var GenresHasCategoriesRule $rule
         */
        $this->assertFalse($rule->passes('',[1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->createRuleMock([1,2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id'=>1],
                ['category_id'=>2]
            ]));
        /**
         * @var GenresHasCategoriesRule $rule
         */
        $this->assertTrue($rule->passes('',[1]));
        //-------------------------------------------------------------
        $rule = $this->createRuleMock([1,2,1,2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id'=>1],
                ['category_id'=>2],
                ['category_id'=>1],
                ['category_id'=>2]
            ]));
        /**
         * @var GenresHasCategoriesRule $rule
         */
        $this->assertTrue($rule->passes('',[1,2]));
    }

    protected function createRuleMock(array $categoriesId): MockInterface
    {
        return \Mockery::mock(GenresHasCategoriesRule::class, [$categoriesId])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
