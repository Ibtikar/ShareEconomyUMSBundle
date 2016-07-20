Installation steps
==================
Add to your appkernel the next line
new Ibtikar\ShareEconomyUMSBundle\IbtikarShareEconomyUMSBundle(),

Add this route to your routing file

ibtikar_share_economy_ums:
    resource: "@IbtikarShareEconomyUMSBundle/Resources/config/routing.yml"
    prefix:   /


Update the security.yml file to match this [file](http://github.com/Ibtikar/share-economy-UMS/tree/master/Resources/doc/security.yml)
