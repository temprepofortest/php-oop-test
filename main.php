<?php error_reporting (E_ALL ^ E_NOTICE); ?> // согласен, такой себе способ решения "PHP Notice:  Undefined index", но т.к. в этом примере это не критичное поведение, решил сделать так
<?php

interface IAnimal {

}

interface IMilkProducing { // для тех, кто даёт молочко
    public function getMilk(): int;
}

interface IEggProducing { // для тех, кто несёт яички
    public function getEgg(): int;
}

interface IBarn { // 
    public function addAnimal(Animal $animal): void; // добавить животное в амбар
    public function getCurrentGoodsCount(): array;  // получить текущее кол-во съестного в амбарах
    public function collectGoods(): array;         // собрать продукты

    public function getAnimalCount(): array;      // получить кол-во животных в амбаре
}

interface IFarm { //
    public function buildBarn(): void; // Создать амбар и добавить его в массив амбаров
    public function addAnimalToBarn(int $barnId, Animal $animal): void; // добавить животное в нужный амбар
    public function getBarnsInfo(): void; // получить информацию об амбарах на ферме

    public function getLastAnimalId(): int; // для уникальности id животных

    public function getGoods():void; // собрать продукты со всех амбаров

    public function getFarmAnimalInfo() : void; // получить информацию о животных на ферме
    public function getFarmGoodsInfo() : void; // получить информацию о продуктах
}

class Animal implements IAnimal {
    public $animalID;

    function __construct($id) {
        $this->animalID = $id;
    }
}

class Cow extends Animal implements IMilkProducing {

    public function getMilk(): int {
        return rand(8, 12);
    }

}

class Hen extends Animal implements IEggProducing {

    public function getEgg(): int {
        return rand(0, 1);
    }

}

class Barn implements IBarn {
    public $barnID;
    public $animals = [];

    public $eggs = 0;
    public $milk = 0;

    function __construct($id) {
        $this->barnID = $id;
    }

    public function addAnimal(Animal $animal): void {
        $this->animals[] = $animal;
    }

    public function getCurrentGoodsCount(): array {
        return [
            'Milk (l)' => $this->milk,
            'Eggs (pcs.)' => $this->eggs,
        ];
    }

    public function collectGoods() : array {
        $animalType = NULL;
        foreach ($this->animals as $animal) {
            $animalType = get_class($animal);

            switch($animalType) {
                case 'Cow':
                    $this->milk += $animal->getMilk();
                    break;
                case 'Hen':
                    $this->eggs += $animal->getEgg();
                    break;
                default:
                    echo 'Wolf in Barn! :O';
            }
        }

        return [
            'Milk (l)' => $this->milk,
            'Eggs (pcs.)' => $this->eggs,
        ];
    }

    public function getAnimalCount() : array {
        $animalsCount = [
            'Cow' => 0,
            'Hen' => 0
        ];

        $animalType = NULL;

        foreach($this->animals as $animal) {
            $animalType = get_class($animal);

            $animalsCount[$animalType] += 1;
        }

        return $animalsCount;
    }
}

class Farm implements IFarm {
    public $farmName;

    private $barnLastId = 0;
    private $animalLastId = 0;

    public $barns = [];

    function __construct($farmName) {
        $this->farmName = $farmName;
    }

    public function buildBarn() : void {
        $this->barns[] = new Barn($this->barnLastId);
        $this->barnLastId++;
    }

    public function addAnimalToBarn(int $barnId, Animal $animal) : void {
        $this->barns[$barnId]->addAnimal($animal);
    }

    public function getLastAnimalId() : int {
        return $this->animalLastId++;
    }

    public function getBarnsInfo() : void {
        $barnIds = array_keys($this->barns);

        $animalsInfoByBarn = NULL;
        echo "The following barns have been built on the farm: \n";
        foreach($barnIds as $barnId) {
            echo "Barn with id" . $barnId . "\n";
            $animalsInfoByBarn = $this->barns[$barnId]->getAnimalCount();
            echo "This barn contains (animal) (animal count): \n";
            foreach($animalsInfoByBarn as $animalType => $animalCount) {
                echo $animalType . " " . $animalCount . "\n";
            }
        }

    }

    public function getGoods(): void {
        foreach($this->barns as $barn) {
            $barn->collectGoods();
        }
    }

    /*
        Решил разделить вывод информации о товарах и животных на 2 метода
    */
    public function getFarmAnimalInfo() : void {
        $animalsInfoByFarm = [];
        $animalsInfoByBarn = [];

        foreach($this->barns as $barn) {
            $animalsInfoByBarn = $barn->getAnimalCount();
            foreach($animalsInfoByBarn as $animalType => $animalCount) {
                $animalsInfoByFarm[$animalType] += $animalCount;
            }
        }

        foreach($animalsInfoByFarm as $animalType => $animalCount) {
            echo $animalType . " " . $animalCount . "\n";
        }
    }

    public function getFarmGoodsInfo() : void {
        $goodsInfoByFarm = [];
        $goodsInfoByBarn = [];

        foreach($this->barns as $barn) {
            $goodsInfoByBarn = $barn->getCurrentGoodsCount();
            foreach($goodsInfoByBarn as $goodsType => $goodsCount) {
                $goodsInfoByFarm[$goodsType] += $goodsCount;
            }
        }

        foreach($goodsInfoByFarm as $goodsType => $goodsCount) {
            echo $goodsType . " " . $goodsCount . "\n";
        }
    }
}

$farm = new Farm("Happy meadow");
echo "Building a barn\n";
$farm->buildBarn();
$farm->getBarnsInfo();

echo "Went to the mart\n";
for($cowCounter= 0; $cowCounter < 10; $cowCounter++)
    $farm->addAnimalToBarn(0, new Cow($farm->getLastAnimalId()));

for($henCounter = 0; $henCounter < 20; $henCounter++)
    $farm->addAnimalToBarn(0, new Hen($farm->getLastAnimalId()));

echo "After purchasing the animals: \n";
$farm->getFarmAnimalInfo();

echo "Collecting products ... \n";
for($day = 0; $day < 7; $day++)
    $farm->getGoods();

echo "Let's see how much we managed to collect: \n";
$farm->getFarmGoodsInfo();

echo "Again to the mart\n";
for($henCounter = 0; $henCounter < 5; $henCounter++)
    $farm->addAnimalToBarn(0, new Hen($farm->getLastAnimalId()));

$farm->addAnimalToBarn(0, new Cow($farm->getLastAnimalId()));

echo "Now our farm has: \n";
$farm->getFarmAnimalInfo();

echo "Collecting goods ... \n";
for($day = 0; $day < 7; $day++)
    $farm->getGoods();

echo "Let's see how much we managed to collect now: \n";
$farm->getFarmGoodsInfo();
