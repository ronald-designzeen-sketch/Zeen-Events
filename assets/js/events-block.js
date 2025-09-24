const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, RangeControl, SelectControl, ToggleControl, TextControl } = wp.components;
const { Fragment } = wp.element;
const { __ } = wp.i18n;

registerBlockType("designzeen/events-grid", {
  title: __("Zeen Events", "designzeen-events"),
  icon: "calendar-alt",
  category: "widgets",
  description: __("Display events in grid, list, or carousel layout", "designzeen-events"),
  keywords: [
    __("events", "designzeen-events"),
    __("calendar", "designzeen-events"),
    __("grid", "designzeen-events")
  ],
  attributes: {
    count: { 
      type: "number", 
      default: 6 
    },
    layout: { 
      type: "string", 
      default: "grid" 
    },
    category: {
      type: "string",
      default: ""
    },
    status: {
      type: "string",
      default: ""
    },
    orderby: {
      type: "string",
      default: "meta_value"
    },
    order: {
      type: "string",
      default: "ASC"
    },
    showPast: {
      type: "boolean",
      default: false
    },
    featured: {
      type: "boolean",
      default: false
    }
  },
  edit: ({ attributes, setAttributes }) => {
    const { 
      count, 
      layout, 
      category, 
      status, 
      orderby, 
      order, 
      showPast, 
      featured 
    } = attributes;

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__("Display Settings", "designzeen-events")} initialOpen={true}>
            <RangeControl
              label={__("Number of Events", "designzeen-events")}
              value={count}
              onChange={(newCount) => setAttributes({ count: newCount })}
              min={1}
              max={20}
            />
            <SelectControl
              label={__("Layout Style", "designzeen-events")}
              value={layout}
              options={[
                { label: __("Grid", "designzeen-events"), value: "grid" },
                { label: __("List", "designzeen-events"), value: "list" },
                { label: __("Carousel", "designzeen-events"), value: "carousel" },
              ]}
              onChange={(newLayout) => setAttributes({ layout: newLayout })}
            />
          </PanelBody>
          
          <PanelBody title={__("Filter Settings", "designzeen-events")}>
            <TextControl
              label={__("Category Slug", "designzeen-events")}
              value={category}
              onChange={(newCategory) => setAttributes({ category: newCategory })}
              help={__("Enter category slug to filter events", "designzeen-events")}
            />
            <SelectControl
              label={__("Event Status", "designzeen-events")}
              value={status}
              options={[
                { label: __("All", "designzeen-events"), value: "" },
                { label: __("Upcoming", "designzeen-events"), value: "upcoming" },
                { label: __("Ongoing", "designzeen-events"), value: "ongoing" },
                { label: __("Completed", "designzeen-events"), value: "completed" },
                { label: __("Cancelled", "designzeen-events"), value: "cancelled" },
              ]}
              onChange={(newStatus) => setAttributes({ status: newStatus })}
            />
            <ToggleControl
              label={__("Show Past Events", "designzeen-events")}
              checked={showPast}
              onChange={(newShowPast) => setAttributes({ showPast: newShowPast })}
            />
            <ToggleControl
              label={__("Featured Events Only", "designzeen-events")}
              checked={featured}
              onChange={(newFeatured) => setAttributes({ featured: newFeatured })}
            />
          </PanelBody>
          
          <PanelBody title={__("Order Settings", "designzeen-events")}>
            <SelectControl
              label={__("Order By", "designzeen-events")}
              value={orderby}
              options={[
                { label: __("Event Date", "designzeen-events"), value: "meta_value" },
                { label: __("Title", "designzeen-events"), value: "title" },
                { label: __("Date Published", "designzeen-events"), value: "date" },
                { label: __("Random", "designzeen-events"), value: "rand" },
              ]}
              onChange={(newOrderby) => setAttributes({ orderby: newOrderby })}
            />
            <SelectControl
              label={__("Order Direction", "designzeen-events")}
              value={order}
              options={[
                { label: __("Ascending", "designzeen-events"), value: "ASC" },
                { label: __("Descending", "designzeen-events"), value: "DESC" },
              ]}
              onChange={(newOrder) => setAttributes({ order: newOrder })}
            />
          </PanelBody>
        </InspectorControls>
        
        <div className="dz-events-placeholder">
          <div className="dz-events-preview-header">
            <span className="dashicons dashicons-calendar-alt"></span>
            <h3>{__("Zeen Events", "designzeen-events")}</h3>
          </div>
          <div className="dz-events-preview-content">
            <p><strong>{__("Display Settings:", "designzeen-events")}</strong></p>
            <ul>
              <li>{__("Count:", "designzeen-events")} {count} {__("events", "designzeen-events")}</li>
              <li>{__("Layout:", "designzeen-events")} <strong>{layout}</strong></li>
              {category && <li>{__("Category:", "designzeen-events")} {category}</li>}
              {status && <li>{__("Status:", "designzeen-events")} {status}</li>}
              <li>{__("Order:", "designzeen-events")} {orderby} ({order})</li>
              {showPast && <li>{__("Including past events", "designzeen-events")}</li>}
              {featured && <li>{__("Featured events only", "designzeen-events")}</li>}
            </ul>
          </div>
        </div>
      </Fragment>
    );
  },
  save: () => null, // PHP render handles frontend
});

