Installation steps
==================

1.In your project composer.json file "extra" section add the following information

    "extra": {
        "installer-paths": {
            "src/Ibtikar/ShareEconomyUMSBundle/": ["Ibtikar/ShareEconomyUMSBundle"]
        }
    }

2.Require the package using composer by running

    composer require Ibtikar/ShareEconomyUMSBundle

3.Add to your appkernel the next line
    new Ibtikar\ShareEconomyUMSBundle\IbtikarShareEconomyUMSBundle(),

4.Add this route to your routing file

    ibtikar_share_economy_ums:
        resource: "@IbtikarShareEconomyUMSBundle/Resources/config/routing.yml"
        prefix:   /


5.Update the security.yml file to match this [file](http://github.com/Ibtikar/ShareEconomyUMSBundle/tree/master/Resources/doc/security.yml)

6.Configure the bundle templates: add the following lines to your project config.yml

    ibtikar_share_economy_ums:
        frontend_layout: "AppBundle:Frontend:layout.html.twig"
        max_daily_forget_passwords_requests: 5
        max_daily_verification_code_requests: 5
        max_daily_verification_email_requests: 5
        verification_code_expiry_minutes: 5
        user_class: AppBundle\Entity\User

7.Add the next line to your .gitignore file

    /src/Ibtikar/ShareEconomyUMSBundle

8.Run doctrine migrations command

    bin/console doctrine:migrations:migrate --configuration=src/Ibtikar/ShareEconomyUMSBundle/Resources/config/migrations.yml