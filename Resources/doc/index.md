Installation steps
==================

1.In your project composer.json file "extra" section add the following information

    "extra": {
        "installer-paths": {
            "src/Ibtikar/ShareEconomyUMSBundle/": ["Ibtikar/share-economy-UMS"]
        }
    }

2.Require the package using composer by running

    composer require Ibtikar/share-economy-UMS

3.Add to your appkernel the next line
    new Ibtikar\ShareEconomyUMSBundle\IbtikarShareEconomyUMSBundle(),

4.Add this route to your routing file

    ibtikar_share_economy_ums:
        resource: "@IbtikarShareEconomyUMSBundle/Resources/config/routing.yml"
        prefix:   /


5.Update the security.yml file to match this [file](http://github.com/Ibtikar/share-economy-UMS/tree/master/Resources/doc/security.yml)

6.In config.yml configure the new custom MySQL functions needed
    
    dql:
        datetime_functions:
          DATE: DoctrineExtensions\Query\Mysql\Date

7.Configure the bundle templates: add the following lines to your project config.yml
    
    ibtikar_share_economy_cms:
        frontend_layout: "AppBundle:Frontend:layout.html.twig"

8.Add the next line to your .gitignore file

    /src/Ibtikar/ShareEconomyUMSBundle

9.Run doctrine migrations command

    bin/console doctrine:migrations:migrate --configuration=src/Ibtikar/ShareEconomyUMSBundle/Resources/config/migrations.yml