define([
    'ko',
    'uiComponent',
    'Simmons_StoreLocator/js/cmsmart_localtor'
], function(ko, Component, updateMap) {
    var MAPS_DIRECTION_URL = 'https://www.google.com/maps/dir/?api=1&destination={destination}';

    return Component.extend({
        mapConfig: {},

        storeList: ko.observableArray([]),

        filteredStores: ko.observableArray([]),

        closestStores: ko.observableArray([]),

        filter: ko.observable(''),

        filterList: function(list) {
            var filteredStoreList = list.reduce(function (r, a) {
                r[a.store_name] = r[a.store_name] || [];
                r[a.store_name].push(a);
                return r;
            }, Object.create(null));

            this.filteredStores(filteredStoreList);

            var closestStores = Object.values(filteredStoreList).map(function (store) {
                return Object.values(store)[0];
            });

            this.closestStores(closestStores);
        },

        changeFilter: function(filter) {
            event.preventDefault();
            event.stopPropagation();
            this.filter(filter);
        },

        updateStoreList: function(storeList) {
            var storeListToUpdate = storeList ? storeList : [];
            this.storeList(storeListToUpdate);
        },

        addMapConfig: function(config) {
            for (var property in config) {
                this.mapConfig[property] = config[property];
            }
        },

        updateMap: function() {
            if (!this.storeList().length) return;
            var requestedLocation = new google.maps.LatLng(this.storeList()[0].latitude, this.storeList()[0].longitude);
            updateMap(this.mapConfig, requestedLocation);
        },

        toPositionJson: function(store) {
            var position = {};
            position.lat = +store.latitude;
            position.lng = +store.longitude;
            position = JSON.stringify(position);

            return position;
        },

        toStoreJsonData: function(store) {
            var storeInfo = {};
            storeInfo['store_name'] = store['store_name'];
            storeInfo['store_link'] = store['store_link'];
            storeInfo['phone_number'] = store['phone_number'];
            storeInfo['fax_number'] = store['fax_number'];
            storeInfo['address'] = store['address'];

            storeInfo['store_id'] = parseInt(store['localtor_id']);
            storeInfo = JSON.stringify(storeInfo);

            return storeInfo;
        },

        toZoonLevelJsonData: function(store) {
            var level = {};
            level['zoom_level'] = parseInt(store['zoom_level']);
            level = JSON.stringify(level);

            return level;
        },

        urlToMaps: function (lat, lon) {
            return MAPS_DIRECTION_URL
                .replace('{destination}', encodeURIComponent(lat + ',' + lon));
        }
    });
});
